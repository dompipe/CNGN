<?php
require_once __DIR__ . '/cngn_science.php';

$cngn = new CNGNScience(16);

// Example 1: ask for a desired physics capability. CNGN composes the
// prerequisite gravitational parameter automatically before orbital speed.
$orbit = $cngn->make(array(
    'goal' => 'orbital_speed',
    'domain' => 'physics',
    'problem' => 'gravitation',
    'descriptors' => array('circular orbit', 'requires gravity'),
), array(
    'central_mass' => 5.97219e24,
    'radius' => 6.371e6 + 4.0e5,
));

// Example 2: electric power depends on voltage. The planner selects Ohm's law
// to provide voltage, then uses P=VI.
$power = $cngn->make(array(
    'goal' => 'electric_power',
    'domain' => 'physics',
    'problem' => 'electromagnetism',
    'descriptors' => array('electric power', 'requires voltage'),
), array(
    'current' => 2.0,
    'resistance' => 12.0,
));

// Example 3: population variance depends on the arithmetic mean.
$variance = $cngn->make(array(
    'goal' => 'variance',
    'domain' => 'math',
    'problem' => 'statistics',
    'descriptors' => array('population variance', 'requires mean'),
), array(
    'values' => array(2, 4, 4, 4, 5, 5, 7, 9),
));

header('Content-Type: application/json; charset=utf-8');
echo json_encode(array(
    'orbit' => $orbit,
    'electric_power' => $power,
    'variance' => $variance,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
