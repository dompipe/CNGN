<?php
/**
 * Deterministic math + physics catalog for CNGN algorithm composition.
 * Requires algorithm_taxonomy.php.
 */

require_once __DIR__ . '/algorithm_taxonomy.php';

class CNGNMathPhysicsCatalog
{
    public static function build()
    {
        $r = new CNGNAlgorithmRegistry();

        // -----------------------------
        // Mathematics
        // -----------------------------
        $r->register(array(
            'id' => 'math.add',
            'path' => array('math','arithmetic','addition','direct','sum','scalar','php-native','scalar-result'),
            'inputs' => array('a','b'),
            'provides' => array('sum'),
            'descriptors' => array('scalar input','exact arithmetic','binary operation'),
            'complexity' => 'constant time',
            'opcode' => '010111',
            'executor' => function(array $s) { $s['sum'] = $s['a'] + $s['b']; return $s; },
        ));

        $r->register(array(
            'id' => 'math.power',
            'path' => array('math','arithmetic','exponentiation','direct','power','real scalar','php-native','scalar-result'),
            'inputs' => array('base','exponent'),
            'provides' => array('power'),
            'descriptors' => array('scalar input','power operation','deterministic numeric'),
            'complexity' => 'constant time',
            'opcode' => '010110',
            'executor' => function(array $s) { $s['power'] = pow($s['base'], $s['exponent']); return $s; },
        ));

        $r->register(array(
            'id' => 'math.linear.solve',
            'path' => array('math','algebra','linear-equation','analytic','isolate-variable','single variable','php-native','scalar-solution'),
            'inputs' => array('a','b'),
            'provides' => array('x'),
            'descriptors' => array('linear equation','single unknown','exact solution','nonzero coefficient'),
            'constraints' => array('a != 0'),
            'executor' => function(array $s) {
                if ((float)$s['a'] == 0.0) throw new InvalidArgumentException('Linear solve requires a != 0.');
                // ax + b = 0
                $s['x'] = -$s['b'] / $s['a'];
                return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.quadratic.solve',
            'path' => array('math','algebra','quadratic-equation','analytic','quadratic-formula','real-or-complex','php-native','root-pair'),
            'inputs' => array('a','b','c'),
            'provides' => array('roots'),
            'descriptors' => array('quadratic equation','two roots','complex aware','nonzero coefficient'),
            'constraints' => array('a != 0'),
            'executor' => function(array $s) {
                if ((float)$s['a'] == 0.0) throw new InvalidArgumentException('Quadratic solve requires a != 0.');
                $d = ($s['b'] * $s['b']) - (4 * $s['a'] * $s['c']);
                if ($d >= 0) {
                    $root = sqrt($d);
                    $s['roots'] = array((-$s['b'] + $root)/(2*$s['a']), (-$s['b'] - $root)/(2*$s['a']));
                } else {
                    $real = -$s['b']/(2*$s['a']);
                    $imag = sqrt(-$d)/(2*$s['a']);
                    $s['roots'] = array(array('real'=>$real,'imag'=>$imag), array('real'=>$real,'imag'=>-$imag));
                }
                $s['discriminant'] = $d;
                return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.mean',
            'path' => array('math','statistics','central-tendency','aggregate','arithmetic-mean','finite sample','php-native','mean-scalar'),
            'inputs' => array('values'),
            'provides' => array('mean'),
            'descriptors' => array('finite sample','central tendency','linear pass'),
            'complexity' => 'linear time',
            'executor' => function(array $s) {
                if (!is_array($s['values']) || count($s['values']) === 0) throw new InvalidArgumentException('Mean requires a non-empty values array.');
                $s['mean'] = array_sum($s['values']) / count($s['values']);
                return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.variance.population',
            'path' => array('math','statistics','dispersion','aggregate','squared-deviation','population','php-native','variance-scalar'),
            'inputs' => array('values'),
            'requires' => array('mean'),
            'provides' => array('variance'),
            'descriptors' => array('population variance','squared deviation','requires mean','linear pass'),
            'complexity' => 'linear time',
            'executor' => function(array $s) {
                $sum = 0.0;
                foreach ($s['values'] as $v) $sum += ($v - $s['mean']) * ($v - $s['mean']);
                $s['variance'] = $sum / count($s['values']);
                return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.vector.dot',
            'path' => array('math','linear-algebra','vector-product','reduction','dot-product','equal length','php-native','dot-scalar'),
            'inputs' => array('vector_a','vector_b'),
            'provides' => array('dot_product'),
            'descriptors' => array('vector input','scalar output','equal dimensions','linear pass'),
            'complexity' => 'linear time',
            'executor' => function(array $s) {
                if (!is_array($s['vector_a']) || !is_array($s['vector_b']) || count($s['vector_a']) !== count($s['vector_b'])) throw new InvalidArgumentException('Dot product requires equal-length vectors.');
                $sum = 0.0;
                foreach ($s['vector_a'] as $i => $v) $sum += $v * $s['vector_b'][$i];
                $s['dot_product'] = $sum;
                return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.vector.magnitude',
            'path' => array('math','linear-algebra','vector-norm','reduction','euclidean-norm','finite vector','php-native','magnitude-scalar'),
            'inputs' => array('vector'),
            'provides' => array('magnitude'),
            'descriptors' => array('vector input','scalar output','euclidean norm','nonnegative output'),
            'complexity' => 'linear time',
            'executor' => function(array $s) {
                if (!is_array($s['vector'])) throw new InvalidArgumentException('Magnitude requires vector array.');
                $sum = 0.0; foreach ($s['vector'] as $v) $sum += $v*$v;
                $s['magnitude'] = sqrt($sum); return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.matrix.multiply',
            'path' => array('math','linear-algebra','matrix-product','nested-loop','row-column-product','dense matrix','php-native','matrix-result'),
            'inputs' => array('matrix_a','matrix_b'),
            'provides' => array('matrix_product'),
            'descriptors' => array('matrix input','dense multiply','dimension checked','cubic worstcase'),
            'complexity' => 'cubic time',
            'executor' => function(array $s) {
                $a=$s['matrix_a']; $b=$s['matrix_b'];
                if (!is_array($a)||!is_array($b)||!count($a)||!count($b)||!is_array($a[0])||!is_array($b[0])) throw new InvalidArgumentException('Matrix multiply requires two rectangular matrices.');
                $aCols=count($a[0]); $bRows=count($b); $bCols=count($b[0]);
                if ($aCols !== $bRows) throw new InvalidArgumentException('Matrix dimensions are incompatible.');
                $out=array();
                foreach ($a as $i=>$row) {
                    if (count($row)!==$aCols) throw new InvalidArgumentException('matrix_a is not rectangular.');
                    $out[$i]=array();
                    for($j=0;$j<$bCols;$j++) { $sum=0.0; for($k=0;$k<$aCols;$k++) $sum += $a[$i][$k]*$b[$k][$j]; $out[$i][$j]=$sum; }
                }
                $s['matrix_product']=$out; return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.derivative.central',
            'path' => array('math','calculus','first-derivative','numerical','central-difference','second-order','php-callable','derivative-scalar'),
            'inputs' => array('fn','x','h'),
            'provides' => array('derivative'),
            'descriptors' => array('numerical derivative','central difference','second order','requires step'),
            'complexity' => 'constant time',
            'executor' => function(array $s) {
                if (!is_callable($s['fn'])) throw new InvalidArgumentException('Derivative requires callable fn.');
                if ((float)$s['h'] == 0.0) throw new InvalidArgumentException('Derivative step h must be nonzero.');
                $f=$s['fn']; $s['derivative']=($f($s['x']+$s['h'])-$f($s['x']-$s['h']))/(2*$s['h']); return $s;
            },
        ));

        $r->register(array(
            'id' => 'math.integral.trapezoid',
            'path' => array('math','calculus','definite-integral','numerical','trapezoid-rule','uniform partition','php-callable','integral-scalar'),
            'inputs' => array('fn','lower','upper','steps'),
            'provides' => array('integral'),
            'descriptors' => array('numerical integral','trapezoid rule','bounded interval','uniform partition'),
            'complexity' => 'linear time',
            'executor' => function(array $s) {
                if (!is_callable($s['fn'])) throw new InvalidArgumentException('Integral requires callable fn.');
                $n=(int)$s['steps']; if($n<1) throw new InvalidArgumentException('Integral steps must be >= 1.');
                $f=$s['fn']; $h=($s['upper']-$s['lower'])/$n; $sum=0.5*($f($s['lower'])+$f($s['upper']));
                for($i=1;$i<$n;$i++) $sum += $f($s['lower']+$i*$h);
                $s['integral']=$sum*$h; return $s;
            },
        ));

        // -----------------------------
        // Physics
        // -----------------------------
        $r->register(array(
            'id' => 'physics.newton.force',
            'path' => array('physics','mechanics','newton-second-law','analytic','mass-acceleration-product','scalar one-axis','php-native','force-scalar'),
            'inputs' => array('mass','acceleration'),
            'provides' => array('force'),
            'descriptors' => array('newton second law','mass acceleration','classical mechanics','scalar force'),
            'executor' => function(array $s) { $s['force']=$s['mass']*$s['acceleration']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.gravity.force',
            'path' => array('physics','gravitation','newtonian-gravity','analytic','inverse-square-force','two body','php-native','force-scalar'),
            'inputs' => array('mass_1','mass_2','distance'),
            'provides' => array('force'),
            'descriptors' => array('inverse square','two masses','newtonian gravity','attractive force'),
            'constraints' => array('distance > 0'),
            'executor' => function(array $s) {
                if ((float)$s['distance'] <= 0.0) throw new InvalidArgumentException('Gravity distance must be > 0.');
                $G=6.67430e-11; $s['force']=$G*$s['mass_1']*$s['mass_2']/($s['distance']*$s['distance']); return $s;
            },
        ));

        $r->register(array(
            'id' => 'physics.gravity.parameter',
            'path' => array('physics','gravitation','orbital-mechanics','prepare','gravitational-parameter','central body','php-native','parameter-scalar'),
            'inputs' => array('central_mass'),
            'provides' => array('gravitational_parameter'),
            'descriptors' => array('central mass','gravity parameter','orbital prerequisite','unit m3s2'),
            'executor' => function(array $s) { $s['gravitational_parameter']=6.67430e-11*$s['central_mass']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.orbit.circular-speed',
            'path' => array('physics','gravitation','orbital-mechanics','solve','circular-orbit-speed','point mass','php-native','speed-scalar'),
            'inputs' => array('radius'),
            'requires' => array('gravitational_parameter'),
            'provides' => array('orbital_speed'),
            'descriptors' => array('circular orbit','requires gravity','central body','speed output'),
            'executor' => function(array $s) {
                if ((float)$s['radius'] <= 0.0) throw new InvalidArgumentException('Orbital radius must be > 0.');
                $s['orbital_speed']=sqrt($s['gravitational_parameter']/$s['radius']); return $s;
            },
        ));

        $r->register(array(
            'id' => 'physics.momentum',
            'path' => array('physics','mechanics','momentum','analytic','mass-velocity-product','scalar one-axis','php-native','momentum-scalar'),
            'inputs' => array('mass','velocity'),
            'provides' => array('momentum'),
            'descriptors' => array('mass velocity','linear momentum','classical mechanics','scalar output'),
            'executor' => function(array $s) { $s['momentum']=$s['mass']*$s['velocity']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.kinetic-energy',
            'path' => array('physics','mechanics','energy','analytic','kinetic-energy','nonrelativistic','php-native','energy-scalar'),
            'inputs' => array('mass','velocity'),
            'provides' => array('kinetic_energy'),
            'descriptors' => array('kinetic energy','velocity squared','classical mechanics','nonnegative energy'),
            'executor' => function(array $s) { $s['kinetic_energy']=0.5*$s['mass']*$s['velocity']*$s['velocity']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.kinematics.position',
            'path' => array('physics','mechanics','constant-acceleration','analytic','position-update','one dimensional','php-native','position-scalar'),
            'inputs' => array('position_0','velocity_0','acceleration','time'),
            'provides' => array('position'),
            'descriptors' => array('constant acceleration','kinematic position','one dimensional','time evolution'),
            'executor' => function(array $s) { $s['position']=$s['position_0']+$s['velocity_0']*$s['time']+0.5*$s['acceleration']*$s['time']*$s['time']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.coulomb.force',
            'path' => array('physics','electromagnetism','electrostatic-force','analytic','inverse-square-charge','two charge','php-native','force-scalar'),
            'inputs' => array('charge_1','charge_2','distance'),
            'provides' => array('force'),
            'descriptors' => array('inverse square','two charges','coulomb law','electrostatic force'),
            'constraints' => array('distance > 0'),
            'executor' => function(array $s) {
                if ((float)$s['distance'] <= 0.0) throw new InvalidArgumentException('Coulomb distance must be > 0.');
                $k=8.9875517923e9; $s['force']=$k*$s['charge_1']*$s['charge_2']/($s['distance']*$s['distance']); return $s;
            },
        ));

        $r->register(array(
            'id' => 'physics.ohm.voltage',
            'path' => array('physics','electromagnetism','circuit-law','analytic','ohm-voltage','resistive dc','php-native','voltage-scalar'),
            'inputs' => array('current','resistance'),
            'provides' => array('voltage'),
            'descriptors' => array('ohm law','current resistance','resistive circuit','voltage output'),
            'executor' => function(array $s) { $s['voltage']=$s['current']*$s['resistance']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.electric-power',
            'path' => array('physics','electromagnetism','circuit-power','analytic','voltage-current-product','dc scalar','php-native','power-scalar'),
            'inputs' => array('current'),
            'requires' => array('voltage'),
            'provides' => array('electric_power'),
            'descriptors' => array('electric power','requires voltage','current product','circuit output'),
            'executor' => function(array $s) { $s['electric_power']=$s['voltage']*$s['current']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.wave.speed',
            'path' => array('physics','waves','wave-relation','analytic','frequency-wavelength-product','scalar wave','php-native','speed-scalar'),
            'inputs' => array('frequency','wavelength'),
            'provides' => array('wave_speed'),
            'descriptors' => array('wave speed','frequency wavelength','scalar relation','positive magnitude'),
            'executor' => function(array $s) { $s['wave_speed']=$s['frequency']*$s['wavelength']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.photon.energy',
            'path' => array('physics','quantum','photon-energy','analytic','planck-frequency-product','single photon','php-native','energy-scalar'),
            'inputs' => array('frequency'),
            'provides' => array('photon_energy'),
            'descriptors' => array('planck relation','photon energy','frequency input','quantum relation'),
            'executor' => function(array $s) { $s['photon_energy']=6.62607015e-34*$s['frequency']; return $s; },
        ));

        $r->register(array(
            'id' => 'physics.ideal-gas.pressure',
            'path' => array('physics','thermodynamics','ideal-gas-law','analytic','solve-pressure','ideal gas','php-native','pressure-scalar'),
            'inputs' => array('moles','temperature','volume'),
            'provides' => array('pressure'),
            'descriptors' => array('ideal gas','state equation','temperature volume','pressure output'),
            'constraints' => array('volume > 0'),
            'executor' => function(array $s) {
                if ((float)$s['volume'] <= 0.0) throw new InvalidArgumentException('Ideal gas volume must be > 0.');
                $s['pressure']=$s['moles']*8.31446261815324*$s['temperature']/$s['volume']; return $s;
            },
        ));

        return $r;
    }

    public static function engine()
    {
        return new CNGNAlgorithmEngine(self::build());
    }
}
