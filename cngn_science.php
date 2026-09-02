<?php
/**
 * High-level CNGN science facade.
 *
 * Preserves the original CNGN binary/math engine while exposing the semantic
 * algorithm taxonomy, deterministic composer, physics/math catalog, and LaTeX.
 */

require_once __DIR__ . '/cngn.php';
require_once __DIR__ . '/algorithm_taxonomy.php';
require_once __DIR__ . '/math_physics_catalog.php';
require_once __DIR__ . '/latex.php';
require_once __DIR__ . '/opcode_latex.php';

class CNGNScience extends CNGN
{
    private $algorithmRegistry;
    private $latexEngine;

    public function __construct($index_cnt = 16)
    {
        parent::__construct((float)$index_cnt);
        $this->algorithmRegistry = CNGNMathPhysicsCatalog::build();
        $this->latexEngine = new CNGNLaTeXEngine($this->algorithmRegistry);
    }

    public function algorithmRegistry()
    {
        return $this->algorithmRegistry;
    }

    public function algorithmTaxonomy()
    {
        return $this->algorithmRegistry->taxonomy();
    }

    public function describeAlgorithm($partId)
    {
        $part = $this->algorithmRegistry->part($partId);
        if (!$part) {
            return null;
        }
        $copy = $part;
        unset($copy['executor']);
        $copy['latex'] = CNGNLaTeXRenderer::part($part);
        $copy['lineage'] = $this->algorithmRegistry->taxonomy()->lineage($part['taxonomy_id']);
        return $copy;
    }

    public function describeOpcode($opcode)
    {
        return CNGNOpcodeLaTeX::describe($opcode);
    }

    public function opcodeCatalog()
    {
        return CNGNOpcodeLaTeX::all();
    }

    public function composeAlgorithm(array $desire)
    {
        return $this->latexEngine->compose($desire);
    }

    public function runAlgorithm($planOrComposition, array $inputs)
    {
        $plan = $planOrComposition;
        if (is_array($planOrComposition) && isset($planOrComposition['plan'])) {
            $plan = $planOrComposition['plan'];
        }
        if (!($plan instanceof CNGNAlgorithmPlan)) {
            throw new InvalidArgumentException('runAlgorithm expects a CNGNAlgorithmPlan or composeAlgorithm result.');
        }
        return $this->latexEngine->run($plan, $inputs);
    }

    public function make(array $desire, array $inputs)
    {
        $composition = $this->composeAlgorithm($desire);
        $execution = $this->runAlgorithm($composition, $inputs);
        return array(
            'desire' => $desire,
            'composition' => $composition,
            'execution' => $execution,
        );
    }

    public function algorithms()
    {
        $out = array();
        foreach ($this->algorithmRegistry->all() as $part) {
            $copy = $part;
            unset($copy['executor']);
            $copy['latex'] = CNGNLaTeXRenderer::part($part);
            $out[] = $copy;
        }
        return $out;
    }
}
