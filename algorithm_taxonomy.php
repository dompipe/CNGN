<?php
/**
 * CNGN semantic algorithm taxonomy and deterministic composer.
 *
 * This layer does not replace the original binary opcode engine in cngn.php.
 * It describes algorithms as a taxonomy of parts, then builds a dependency-
 * ordered plan from a desired result. Numeric execution remains deterministic.
 *
 * PHP 7.2 compatible.
 */

class CNGNAlgorithmTaxonomy implements JsonSerializable
{
    const RANKS = array(
        'domain',
        'problem',
        'method',
        'stage',
        'operation',
        'variant',
        'implementation',
        'output',
    );

    private $nodes = array();
    private $children = array();

    public static function slug($value)
    {
        $value = strtolower(trim((string)$value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string)$value, '-');
    }

    public static function normalizeDescriptors(array $values)
    {
        $out = array();
        foreach ($values as $value) {
            $value = trim(preg_replace('/\s+/', ' ', strtolower((string)$value)));
            if ($value === '') {
                continue;
            }
            $out[$value] = true;
        }
        return array_keys($out);
    }

    public function ensurePath(array $path, array $leafMeta = array())
    {
        if (count($path) > count(self::RANKS)) {
            throw new InvalidArgumentException('Algorithm taxonomy paths have at most eight ranks.');
        }
        $parent = null;
        $parts = array();
        $last = null;
        foreach (array_values($path) as $depth => $name) {
            $name = trim((string)$name);
            if ($name === '') {
                throw new InvalidArgumentException('Algorithm taxonomy rank names cannot be empty.');
            }
            $parts[] = self::slug($name);
            $id = implode('/', $parts);
            $rank = self::RANKS[$depth];
            $meta = ($depth === count($path) - 1) ? $leafMeta : array();
            if (!isset($this->nodes[$id])) {
                $this->nodes[$id] = array(
                    'id' => $id,
                    'rank' => $rank,
                    'name' => $name,
                    'parent_id' => $parent,
                    'depth' => $depth,
                    'descriptors' => self::normalizeDescriptors(isset($meta['descriptors']) ? $meta['descriptors'] : array()),
                    'meta' => $meta,
                );
                if ($parent !== null) {
                    if (!isset($this->children[$parent])) {
                        $this->children[$parent] = array();
                    }
                    $this->children[$parent][$id] = true;
                }
            } elseif ($meta) {
                $this->nodes[$id]['descriptors'] = self::normalizeDescriptors(array_merge(
                    $this->nodes[$id]['descriptors'],
                    isset($meta['descriptors']) ? $meta['descriptors'] : array()
                ));
                $this->nodes[$id]['meta'] = array_merge($this->nodes[$id]['meta'], $meta);
            }
            $parent = $id;
            $last = $id;
        }
        return $last;
    }

    public function node($id)
    {
        return isset($this->nodes[$id]) ? $this->nodes[$id] : null;
    }

    public function childrenOf($id)
    {
        $out = array();
        foreach (array_keys(isset($this->children[$id]) ? $this->children[$id] : array()) as $childId) {
            $out[] = $this->nodes[$childId];
        }
        return $out;
    }

    public function lineage($id)
    {
        $out = array();
        while ($id !== null && isset($this->nodes[$id])) {
            array_unshift($out, $this->nodes[$id]);
            $id = $this->nodes[$id]['parent_id'];
        }
        return $out;
    }

    public function all()
    {
        return array_values($this->nodes);
    }

    public function jsonSerialize()
    {
        return array(
            'ranks' => self::RANKS,
            'nodes' => array_values($this->nodes),
        );
    }
}

class CNGNAlgorithmRegistry implements JsonSerializable
{
    private $taxonomy;
    private $parts = array();

    public function __construct(CNGNAlgorithmTaxonomy $taxonomy = null)
    {
        $this->taxonomy = $taxonomy ?: new CNGNAlgorithmTaxonomy();
    }

    public function taxonomy()
    {
        return $this->taxonomy;
    }

    public function register(array $part)
    {
        foreach (array('id', 'path', 'provides', 'executor') as $required) {
            if (!array_key_exists($required, $part)) {
                throw new InvalidArgumentException('Algorithm part missing required field: ' . $required);
            }
        }
        if (!is_callable($part['executor'])) {
            throw new InvalidArgumentException('Algorithm part executor must be callable.');
        }
        if (!is_array($part['path']) || count($part['path']) < 1 || count($part['path']) > 8) {
            throw new InvalidArgumentException('Algorithm part path must contain one through eight taxonomy ranks.');
        }

        $id = CNGNAlgorithmTaxonomy::slug($part['id']);
        $part['id'] = $id;
        $part['inputs'] = isset($part['inputs']) ? array_values(array_unique($part['inputs'])) : array();
        $part['requires'] = isset($part['requires']) ? array_values(array_unique($part['requires'])) : array();
        $part['provides'] = array_values(array_unique($part['provides']));
        $part['descriptors'] = CNGNAlgorithmTaxonomy::normalizeDescriptors(isset($part['descriptors']) ? $part['descriptors'] : array());
        $part['constraints'] = isset($part['constraints']) ? $part['constraints'] : array();
        $part['complexity'] = isset($part['complexity']) ? $part['complexity'] : 'constant time';
        $part['deterministic'] = array_key_exists('deterministic', $part) ? (bool)$part['deterministic'] : true;
        $part['opcode'] = isset($part['opcode']) ? $part['opcode'] : null;
        $part['description'] = isset($part['description']) ? $part['description'] : '';

        $leafId = $this->taxonomy->ensurePath($part['path'], array(
            'descriptors' => $part['descriptors'],
            'part_id' => $id,
            'provides' => $part['provides'],
            'requires' => $part['requires'],
            'inputs' => $part['inputs'],
            'complexity' => $part['complexity'],
            'opcode' => $part['opcode'],
        ));
        $part['taxonomy_id'] = $leafId;
        $this->parts[$id] = $part;
        return $this;
    }

    public function part($id)
    {
        $id = CNGNAlgorithmTaxonomy::slug($id);
        return isset($this->parts[$id]) ? $this->parts[$id] : null;
    }

    public function all()
    {
        return array_values($this->parts);
    }

    public function providers($capability)
    {
        $out = array();
        foreach ($this->parts as $part) {
            if (in_array($capability, $part['provides'], true)) {
                $out[] = $part;
            }
        }
        return $out;
    }

    public function jsonSerialize()
    {
        $parts = array();
        foreach ($this->parts as $part) {
            $copy = $part;
            unset($copy['executor']);
            $parts[] = $copy;
        }
        return array('taxonomy' => $this->taxonomy, 'parts' => $parts);
    }
}

class CNGNAlgorithmPlan implements JsonSerializable
{
    public $goal;
    public $partIds;
    public $inputs;
    public $provides;
    public $desire;

    public function __construct($goal, array $partIds, array $inputs, array $provides, array $desire)
    {
        $this->goal = $goal;
        $this->partIds = array_values($partIds);
        $this->inputs = array_values(array_unique($inputs));
        $this->provides = array_values(array_unique($provides));
        $this->desire = $desire;
    }

    public function jsonSerialize()
    {
        return array(
            'goal' => $this->goal,
            'parts' => $this->partIds,
            'inputs' => $this->inputs,
            'provides' => $this->provides,
            'desire' => $this->desire,
        );
    }
}

class CNGNAlgorithmComposer
{
    private $registry;

    public function __construct(CNGNAlgorithmRegistry $registry)
    {
        $this->registry = $registry;
    }

    private function score(array $part, array $desire)
    {
        $score = 0.0;
        $path = array_map(array('CNGNAlgorithmTaxonomy', 'slug'), $part['path']);
        foreach (array('domain' => 0, 'problem' => 1, 'method' => 2) as $key => $index) {
            if (!empty($desire[$key]) && isset($path[$index]) && $path[$index] === CNGNAlgorithmTaxonomy::slug($desire[$key])) {
                $score += 12.0 - ($index * 2.0);
            }
        }
        $wanted = CNGNAlgorithmTaxonomy::normalizeDescriptors(isset($desire['descriptors']) ? $desire['descriptors'] : array());
        foreach ($wanted as $descriptor) {
            if (in_array($descriptor, $part['descriptors'], true)) {
                $score += 4.0;
            }
        }
        if ($part['deterministic']) {
            $score += 2.0;
        }
        $score -= count($part['requires']) * 0.15;
        $score -= count($part['inputs']) * 0.02;
        return $score;
    }

    private function chooseProvider($capability, array $desire, array $visiting, array &$ordered, array &$selected)
    {
        if (isset($selected[$capability])) {
            return;
        }
        if (isset($visiting[$capability])) {
            throw new RuntimeException('Algorithm capability cycle detected at ' . $capability);
        }
        $visiting[$capability] = true;
        $providers = $this->registry->providers($capability);
        if (!$providers) {
            throw new RuntimeException('No registered algorithm part provides capability: ' . $capability);
        }
        usort($providers, function ($a, $b) use ($desire) {
            $as = $this->score($a, $desire);
            $bs = $this->score($b, $desire);
            if ($as === $bs) {
                return strcmp($a['id'], $b['id']);
            }
            return ($as > $bs) ? -1 : 1;
        });
        $part = $providers[0];
        foreach ($part['requires'] as $requirement) {
            $this->chooseProvider($requirement, $desire, $visiting, $ordered, $selected);
        }
        if (!in_array($part['id'], $ordered, true)) {
            $ordered[] = $part['id'];
        }
        foreach ($part['provides'] as $provided) {
            $selected[$provided] = $part['id'];
        }
    }

    public function compose(array $desire)
    {
        $goal = isset($desire['goal']) ? trim((string)$desire['goal']) : '';
        if ($goal === '') {
            throw new InvalidArgumentException('Algorithm desire requires a goal capability.');
        }
        $ordered = array();
        $selected = array();
        $this->chooseProvider($goal, $desire, array(), $ordered, $selected);

        $inputs = array();
        $provides = array();
        foreach ($ordered as $partId) {
            $part = $this->registry->part($partId);
            $inputs = array_merge($inputs, $part['inputs']);
            $provides = array_merge($provides, $part['provides']);
        }
        // Inputs satisfied by an earlier capability do not need to be supplied by the caller.
        $inputs = array_values(array_diff(array_unique($inputs), array_unique($provides)));
        return new CNGNAlgorithmPlan($goal, $ordered, $inputs, $provides, $desire);
    }
}

class CNGNAlgorithmEngine
{
    private $registry;
    private $composer;

    public function __construct(CNGNAlgorithmRegistry $registry)
    {
        $this->registry = $registry;
        $this->composer = new CNGNAlgorithmComposer($registry);
    }

    public function compose(array $desire)
    {
        return $this->composer->compose($desire);
    }

    public function run(CNGNAlgorithmPlan $plan, array $inputs)
    {
        $state = $inputs;
        $trace = array();
        foreach ($plan->partIds as $partId) {
            $part = $this->registry->part($partId);
            if (!$part) {
                throw new RuntimeException('Unknown algorithm part in plan: ' . $partId);
            }
            foreach ($part['inputs'] as $input) {
                if (!array_key_exists($input, $state)) {
                    throw new InvalidArgumentException('Missing input ' . $input . ' for part ' . $partId);
                }
            }
            foreach ($part['requires'] as $required) {
                if (!array_key_exists($required, $state)) {
                    throw new RuntimeException('Required capability ' . $required . ' was not produced before ' . $partId);
                }
            }
            $before = array_keys($state);
            $result = call_user_func($part['executor'], $state);
            if (!is_array($result)) {
                throw new RuntimeException('Algorithm part ' . $partId . ' must return a state array.');
            }
            $state = $result;
            foreach ($part['provides'] as $provided) {
                if (!array_key_exists($provided, $state)) {
                    throw new RuntimeException('Algorithm part ' . $partId . ' did not provide declared capability ' . $provided);
                }
            }
            $trace[] = array(
                'part' => $partId,
                'taxonomy_id' => $part['taxonomy_id'],
                'descriptors' => $part['descriptors'],
                'opcode' => $part['opcode'],
                'before_keys' => $before,
                'after_keys' => array_keys($state),
            );
        }
        return array(
            'goal' => $plan->goal,
            'result' => array_key_exists($plan->goal, $state) ? $state[$plan->goal] : null,
            'state' => $state,
            'trace' => $trace,
            'plan' => $plan,
        );
    }
}
