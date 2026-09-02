<?php
/**
 * CNGN LaTeX presentation layer.
 *
 * Keeps numerical execution deterministic while adding symbolic, semantic,
 * and evaluated mathematical representations for algorithm parts and plans.
 * PHP 7.2 compatible.
 */

require_once __DIR__ . '/algorithm_taxonomy.php';

class CNGNLaTeX
{
    public static function escapeText($text)
    {
        return strtr((string)$text, array(
            '\\' => '\\textbackslash{}',
            '{' => '\\{',
            '}' => '\\}',
            '$' => '\\$',
            '&' => '\\&',
            '#' => '\\#',
            '_' => '\\_',
            '%' => '\\%',
            '^' => '\\textasciicircum{}',
            '~' => '\\textasciitilde{}',
        ));
    }

    public static function text($text)
    {
        return '\\text{' . self::escapeText($text) . '}';
    }

    public static function identifier($name)
    {
        $map = array(
            'mass' => 'm', 'mass_1' => 'm_1', 'mass_2' => 'm_2',
            'central_mass' => 'M', 'distance' => 'r', 'radius' => 'r',
            'acceleration' => 'a', 'velocity' => 'v', 'velocity_0' => 'v_0',
            'position' => 'x', 'position_0' => 'x_0', 'time' => 't',
            'force' => 'F', 'momentum' => 'p', 'kinetic_energy' => 'K',
            'gravitational_parameter' => '\\mu', 'orbital_speed' => 'v_{orb}',
            'charge_1' => 'q_1', 'charge_2' => 'q_2',
            'current' => 'I', 'resistance' => 'R', 'voltage' => 'V',
            'electric_power' => 'P', 'frequency' => 'f', 'wavelength' => '\\lambda',
            'wave_speed' => 'v', 'photon_energy' => 'E',
            'moles' => 'n', 'temperature' => 'T', 'volume' => 'V', 'pressure' => 'P',
            'a' => 'a', 'b' => 'b', 'c' => 'c', 'x' => 'x', 'h' => 'h',
            'base' => 'b', 'exponent' => 'p', 'power' => 'b^p',
            'sum' => 's', 'mean' => '\\bar{x}', 'variance' => '\\sigma^2',
            'dot_product' => '\\mathbf{a}\\cdot\\mathbf{b}',
            'magnitude' => '\\lVert\\mathbf{v}\\rVert',
            'matrix_product' => 'C', 'derivative' => "f'(x)", 'integral' => 'I_f',
        );
        if (isset($map[$name])) return $map[$name];
        $safe = preg_replace('/[^A-Za-z0-9]+/', '_', (string)$name);
        return '\\mathrm{' . trim($safe, '_') . '}';
    }

    public static function number($value)
    {
        if (is_int($value)) return (string)$value;
        if (is_float($value) || is_numeric($value)) {
            $number = (float)$value;
            if ($number == 0.0) return '0';
            $abs = abs($number);
            if ($abs >= 1.0e6 || $abs < 1.0e-4) {
                $s = sprintf('%.8e', $number);
                list($m, $e) = explode('e', $s);
                $m = rtrim(rtrim($m, '0'), '.');
                return $m . '\\times 10^{' . (int)$e . '}';
            }
            return rtrim(rtrim(sprintf('%.10f', $number), '0'), '.');
        }
        if (is_array($value)) return self::arrayValue($value);
        if (is_bool($value)) return $value ? '\\mathrm{true}' : '\\mathrm{false}';
        return self::text((string)$value);
    }

    public static function arrayValue(array $value)
    {
        if (!$value) return '\\left[\\right]';
        $matrix = is_array(reset($value));
        if ($matrix) {
            $rows = array();
            foreach ($value as $row) {
                if (!is_array($row)) return self::text(json_encode($value));
                $rows[] = implode(' & ', array_map(array(__CLASS__, 'number'), $row));
            }
            return '\\begin{bmatrix}' . implode('\\\\', $rows) . '\\end{bmatrix}';
        }
        return '\\left[' . implode(', ', array_map(array(__CLASS__, 'number'), $value)) . '\\right]';
    }

    public static function descriptor($descriptor)
    {
        $d = strtolower(trim((string)$descriptor));
        $known = array(
            'inverse square' => '\\propto r^{-2}',
            'velocity squared' => '\\propto v^2',
            'nonnegative output' => '\\mathrm{output}\\ge 0',
            'nonnegative energy' => 'E\\ge 0',
            'nonzero coefficient' => 'a\\ne 0',
            'equal dimensions' => '\\dim(\\mathbf a)=\\dim(\\mathbf b)',
            'requires mean' => '\\mathrm{requires}\\;\\bar{x}',
            'requires gravity' => '\\mathrm{requires}\\;\\mu',
            'bounded interval' => 'x\\in[a,b]',
            'requires step' => 'h\\ne 0',
            'two roots' => '\\{x_1,x_2\\}',
            'scalar input' => 'x\\in\\mathbb{R}',
            'scalar output' => 'y\\in\\mathbb{R}',
            'vector input' => '\\mathbf{x}\\in\\mathbb{R}^n',
            'matrix input' => 'A\\in\\mathbb{R}^{m\\times n}',
            'linear pass' => 'T(n)=\\mathcal{O}(n)',
            'constant time' => 'T(n)=\\mathcal{O}(1)',
            'cubic worstcase' => 'T(n)=\\mathcal{O}(n^3)',
            'complex aware' => 'x\\in\\mathbb{C}\\;\\mathrm{allowed}',
            'second order' => '\\mathrm{error}=\\mathcal{O}(h^2)',
        );
        return isset($known[$d]) ? $known[$d] : self::text($descriptor);
    }

    public static function descriptors(array $descriptors)
    {
        return array_map(array(__CLASS__, 'descriptor'), $descriptors);
    }
}

class CNGNLaTeXCatalog
{
    public static function metadata($partId)
    {
        $m = self::all();
        return isset($m[$partId]) ? $m[$partId] : array();
    }

    public static function all()
    {
        return array(
            'math-add' => array('formula'=>'s=a+b','result'=>'sum'),
            'math-power' => array('formula'=>'y=b^p','result'=>'power'),
            'math-linear-solve' => array('formula'=>'ax+b=0\\quad\\Rightarrow\\quad x=-\\frac{b}{a}','result'=>'x'),
            'math-quadratic-solve' => array('formula'=>'x=\\frac{-b\\pm\\sqrt{b^2-4ac}}{2a}','result'=>'roots'),
            'math-mean' => array('formula'=>'\\bar{x}=\\frac{1}{N}\\sum_{i=1}^{N}x_i','result'=>'mean'),
            'math-variance-population' => array('formula'=>'\\sigma^2=\\frac{1}{N}\\sum_{i=1}^{N}(x_i-\\bar{x})^2','result'=>'variance'),
            'math-vector-dot' => array('formula'=>'\\mathbf a\\cdot\\mathbf b=\\sum_{i=1}^{n}a_i b_i','result'=>'dot_product'),
            'math-vector-magnitude' => array('formula'=>'\\lVert\\mathbf v\\rVert=\\sqrt{\\sum_{i=1}^{n}v_i^2}','result'=>'magnitude'),
            'math-matrix-multiply' => array('formula'=>'C_{ij}=\\sum_{k=1}^{n}A_{ik}B_{kj}','result'=>'matrix_product'),
            'math-derivative-central' => array('formula'=>"f'(x)\\approx\\frac{f(x+h)-f(x-h)}{2h}",'result'=>'derivative'),
            'math-integral-trapezoid' => array('formula'=>'\\int_a^b f(x)\\,dx\\approx h\\left[\\frac{f(a)+f(b)}{2}+\\sum_{i=1}^{n-1}f(a+ih)\\right]','result'=>'integral'),
            'physics-newton-force' => array('formula'=>'F=ma','result'=>'force','units'=>'\\mathrm{N}'),
            'physics-gravity-force' => array('formula'=>'F=G\\frac{m_1m_2}{r^2}','result'=>'force','units'=>'\\mathrm{N}','constants'=>array('G'=>'6.67430\\times10^{-11}\\;\\mathrm{m^3\\,kg^{-1}\\,s^{-2}}')),
            'physics-gravity-parameter' => array('formula'=>'\\mu=GM','result'=>'gravitational_parameter','units'=>'\\mathrm{m^3\\,s^{-2}}'),
            'physics-orbit-circular-speed' => array('formula'=>'v_{orb}=\\sqrt{\\frac{\\mu}{r}}','result'=>'orbital_speed','units'=>'\\mathrm{m\\,s^{-1}}'),
            'physics-momentum' => array('formula'=>'p=mv','result'=>'momentum','units'=>'\\mathrm{kg\\,m\\,s^{-1}}'),
            'physics-kinetic-energy' => array('formula'=>'K=\\frac{1}{2}mv^2','result'=>'kinetic_energy','units'=>'\\mathrm{J}'),
            'physics-kinematics-position' => array('formula'=>'x=x_0+v_0t+\\frac{1}{2}at^2','result'=>'position','units'=>'\\mathrm{m}'),
            'physics-coulomb-force' => array('formula'=>'F=k_e\\frac{q_1q_2}{r^2}','result'=>'force','units'=>'\\mathrm{N}','constants'=>array('k_e'=>'8.9875517923\\times10^9\\;\\mathrm{N\\,m^2\\,C^{-2}}')),
            'physics-ohm-voltage' => array('formula'=>'V=IR','result'=>'voltage','units'=>'\\mathrm{V}'),
            'physics-electric-power' => array('formula'=>'P=VI','result'=>'electric_power','units'=>'\\mathrm{W}'),
            'physics-wave-speed' => array('formula'=>'v=f\\lambda','result'=>'wave_speed','units'=>'\\mathrm{m\\,s^{-1}}'),
            'physics-photon-energy' => array('formula'=>'E=hf','result'=>'photon_energy','units'=>'\\mathrm{J}','constants'=>array('h'=>'6.62607015\\times10^{-34}\\;\\mathrm{J\\,s}')),
            'physics-ideal-gas-pressure' => array('formula'=>'P=\\frac{nRT}{V}','result'=>'pressure','units'=>'\\mathrm{Pa}','constants'=>array('R'=>'8.31446261815324\\;\\mathrm{J\\,mol^{-1}\\,K^{-1}}')),
        );
    }
}

class CNGNLaTeXRenderer
{
    public static function part(array $part)
    {
        $meta = CNGNLaTeXCatalog::metadata($part['id']);
        $formula = isset($meta['formula']) ? $meta['formula'] : null;
        $inputs = array();
        foreach ($part['inputs'] as $name) $inputs[$name] = CNGNLaTeX::identifier($name);
        $provides = array();
        foreach ($part['provides'] as $name) $provides[$name] = CNGNLaTeX::identifier($name);
        $requirements = array();
        foreach ($part['requires'] as $name) $requirements[$name] = CNGNLaTeX::identifier($name);
        return array(
            'id' => $part['id'],
            'taxonomy_id' => $part['taxonomy_id'],
            'formula_latex' => $formula,
            'descriptor_latex' => CNGNLaTeX::descriptors($part['descriptors']),
            'input_symbols' => $inputs,
            'provided_symbols' => $provides,
            'required_symbols' => $requirements,
            'units_latex' => isset($meta['units']) ? $meta['units'] : null,
            'constants_latex' => isset($meta['constants']) ? $meta['constants'] : array(),
            'complexity_latex' => self::complexity($part['complexity']),
        );
    }

    public static function complexity($complexity)
    {
        $map = array(
            'constant time' => 'T(n)=\\mathcal{O}(1)',
            'linear time' => 'T(n)=\\mathcal{O}(n)',
            'quadratic time' => 'T(n)=\\mathcal{O}(n^2)',
            'cubic time' => 'T(n)=\\mathcal{O}(n^3)',
            'logarithmic time' => 'T(n)=\\mathcal{O}(\\log n)',
        );
        return isset($map[$complexity]) ? $map[$complexity] : CNGNLaTeX::text($complexity);
    }

    public static function evaluated(array $part, array $before, array $after)
    {
        $meta = CNGNLaTeXCatalog::metadata($part['id']);
        $symbolic = isset($meta['formula']) ? $meta['formula'] : null;
        $substitutions = array();
        foreach ($part['inputs'] as $name) {
            if (array_key_exists($name, $before) && !is_callable($before[$name])) {
                $substitutions[] = CNGNLaTeX::identifier($name) . '=' . CNGNLaTeX::number($before[$name]);
            }
        }
        foreach ($part['requires'] as $name) {
            if (array_key_exists($name, $before) && !is_callable($before[$name])) {
                $substitutions[] = CNGNLaTeX::identifier($name) . '=' . CNGNLaTeX::number($before[$name]);
            }
        }
        $results = array();
        foreach ($part['provides'] as $name) {
            if (array_key_exists($name, $after)) {
                $value = CNGNLaTeX::identifier($name) . '=' . CNGNLaTeX::number($after[$name]);
                if (isset($meta['units'])) $value .= '\\;' . $meta['units'];
                $results[] = $value;
            }
        }
        return array(
            'symbolic_latex' => $symbolic,
            'substitution_latex' => $substitutions ? implode(',\\quad ', $substitutions) : null,
            'result_latex' => $results ? implode(',\\quad ', $results) : null,
        );
    }

    public static function plan(CNGNAlgorithmRegistry $registry, CNGNAlgorithmPlan $plan)
    {
        $steps = array();
        foreach ($plan->partIds as $id) {
            $part = $registry->part($id);
            if ($part) $steps[] = self::part($part);
        }
        $formulas = array();
        foreach ($steps as $step) if (!empty($step['formula_latex'])) $formulas[] = $step['formula_latex'];
        return array(
            'goal_latex' => CNGNLaTeX::identifier($plan->goal),
            'inputs_latex' => array_map(array('CNGNLaTeX', 'identifier'), $plan->inputs),
            'steps' => $steps,
            'chain_latex' => $formulas ? '\\begin{aligned}' . implode('\\\\', $formulas) . '\\end{aligned}' : null,
        );
    }
}

class CNGNLaTeXEngine
{
    private $registry;
    private $engine;

    public function __construct(CNGNAlgorithmRegistry $registry)
    {
        $this->registry = $registry;
        $this->engine = new CNGNAlgorithmEngine($registry);
    }

    public function compose(array $desire)
    {
        $plan = $this->engine->compose($desire);
        return array(
            'plan' => $plan,
            'latex' => CNGNLaTeXRenderer::plan($this->registry, $plan),
        );
    }

    public function run(CNGNAlgorithmPlan $plan, array $inputs)
    {
        // Re-run the same deterministic plan locally so we can retain the full
        // before/after numeric state for each LaTeX substitution trace.
        $state = $inputs;
        $trace = array();
        foreach ($plan->partIds as $partId) {
            $part = $this->registry->part($partId);
            if (!$part) throw new RuntimeException('Unknown algorithm part: ' . $partId);
            foreach ($part['inputs'] as $input) if (!array_key_exists($input, $state)) throw new InvalidArgumentException('Missing input ' . $input . ' for ' . $partId);
            foreach ($part['requires'] as $required) if (!array_key_exists($required, $state)) throw new RuntimeException('Missing required capability ' . $required . ' before ' . $partId);
            $before = $state;
            $state = call_user_func($part['executor'], $state);
            foreach ($part['provides'] as $provided) if (!array_key_exists($provided, $state)) throw new RuntimeException($partId . ' did not provide ' . $provided);
            $trace[] = array(
                'part' => $partId,
                'taxonomy_id' => $part['taxonomy_id'],
                'descriptors' => $part['descriptors'],
                'latex' => array_merge(CNGNLaTeXRenderer::part($part), CNGNLaTeXRenderer::evaluated($part, $before, $state)),
            );
        }
        $result = array_key_exists($plan->goal, $state) ? $state[$plan->goal] : null;
        $goalLatex = CNGNLaTeX::identifier($plan->goal) . '=' . CNGNLaTeX::number($result);
        $last = end($trace);
        if ($last && !empty($last['latex']['units_latex'])) $goalLatex .= '\\;' . $last['latex']['units_latex'];
        return array(
            'goal' => $plan->goal,
            'result' => $result,
            'result_latex' => $goalLatex,
            'state' => $state,
            'trace' => $trace,
            'plan' => $plan,
            'plan_latex' => CNGNLaTeXRenderer::plan($this->registry, $plan),
        );
    }
}
