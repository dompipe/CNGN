<?php
/**
 * Symbolic/LaTeX metadata for the original CNGN 6-bit opcode surface.
 * This does not change opcode execution in cngn.php.
 */

class CNGNOpcodeLaTeX
{
    private static function table()
    {
        return array(
            '000000'=>array('name'=>'hyperbolic cosine','latex'=>'\\cosh(x)','descriptors'=>array('hyperbolic function','scalar input')),
            '000001'=>array('name'=>'cosine','latex'=>'\\cos(x)','descriptors'=>array('trigonometric function','scalar input')),
            '000010'=>array('name'=>'hyperbolic sine','latex'=>'\\sinh(x)','descriptors'=>array('hyperbolic function','scalar input')),
            '000011'=>array('name'=>'sine','latex'=>'\\sin(x)','descriptors'=>array('trigonometric function','scalar input')),
            '000100'=>array('name'=>'hyperbolic tangent','latex'=>'\\tanh(x)','descriptors'=>array('hyperbolic function','scalar input')),
            '000101'=>array('name'=>'tangent','latex'=>'\\tan(x)','descriptors'=>array('trigonometric function','scalar input')),
            '000110'=>array('name'=>'secant','latex'=>'\\sec(x)','descriptors'=>array('reciprocal trig','scalar input')),
            '000111'=>array('name'=>'cosecant','latex'=>'\\csc(x)','descriptors'=>array('reciprocal trig','scalar input')),
            '001000'=>array('name'=>'cotangent','latex'=>'\\cot(x)','descriptors'=>array('reciprocal trig','scalar input')),
            '001001'=>array('name'=>'arcsine','latex'=>'\\arcsin(x)','descriptors'=>array('inverse trig','principal value')),
            '001010'=>array('name'=>'arccosine','latex'=>'\\arccos(x)','descriptors'=>array('inverse trig','principal value')),
            '001011'=>array('name'=>'arctangent','latex'=>'\\arctan(x)','descriptors'=>array('inverse trig','principal value')),
            '001100'=>array('name'=>'inverse sine form','latex'=>'\\cos(x)','descriptors'=>array('legacy opcode','implementation defined')),
            '001101'=>array('name'=>'inverse cosine form','latex'=>'\\tan(x)','descriptors'=>array('legacy opcode','implementation defined')),
            '001110'=>array('name'=>'inverse cotangent form','latex'=>'\\cot(x)','descriptors'=>array('legacy opcode','implementation defined')),
            '001111'=>array('name'=>'constant rule','latex'=>'\\frac{d}{dx}c=0','descriptors'=>array('derivative rule','constant input')),
            '010000'=>array('name'=>'sum rule','latex'=>'\\frac{d}{dx}(f+g)=f\' + g\'','descriptors'=>array('derivative rule','linear operator')),
            '010001'=>array('name'=>'difference rule','latex'=>'\\frac{d}{dx}(f-g)=f\' - g\'','descriptors'=>array('derivative rule','linear operator')),
            '010010'=>array('name'=>'power rule','latex'=>'\\frac{d}{dx}x^n=nx^{n-1}','descriptors'=>array('derivative rule','power function')),
            '010011'=>array('name'=>'product rule','latex'=>'\\frac{d}{dx}(fg)=f\'g+fg\'','descriptors'=>array('derivative rule','product input')),
            '010100'=>array('name'=>'quotient rule','latex'=>'\\frac{d}{dx}\\left(\\frac{f}{g}\\right)=\\frac{f\'g-fg\'}{g^2}','descriptors'=>array('derivative rule','nonzero denominator')),
            '010101'=>array('name'=>'chain rule','latex'=>'\\frac{d}{dx}f(g(x))=f\'(g(x))g\'(x)','descriptors'=>array('derivative rule','function composition')),
            '010110'=>array('name'=>'exponent','latex'=>'x^y','descriptors'=>array('power operation','binary numeric')),
            '010111'=>array('name'=>'addition','latex'=>'x+y','descriptors'=>array('arithmetic operator','binary numeric')),
            '011000'=>array('name'=>'subtraction','latex'=>'x-y','descriptors'=>array('arithmetic operator','binary numeric')),
            '011001'=>array('name'=>'multiplication','latex'=>'xy','descriptors'=>array('arithmetic operator','binary numeric')),
            '011010'=>array('name'=>'division','latex'=>'\\frac{x}{y}','descriptors'=>array('arithmetic operator','nonzero denominator')),
            '011011'=>array('name'=>'greater than','latex'=>'x>y','descriptors'=>array('comparison operator','boolean output')),
            '011100'=>array('name'=>'less than','latex'=>'x<y','descriptors'=>array('comparison operator','boolean output')),
            '011101'=>array('name'=>'greater or equal','latex'=>'x\\ge y','descriptors'=>array('comparison operator','boolean output')),
            '011110'=>array('name'=>'less or equal','latex'=>'x\\le y','descriptors'=>array('comparison operator','boolean output')),
            '011111'=>array('name'=>'not equal','latex'=>'x\\ne y','descriptors'=>array('comparison operator','boolean output')),
            '100000'=>array('name'=>'equal','latex'=>'x=y','descriptors'=>array('comparison operator','boolean output')),
            '100001'=>array('name'=>'and equal','latex'=>'C\\land(x=y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100010'=>array('name'=>'and not equal','latex'=>'C\\land(x\\ne y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100011'=>array('name'=>'and greater','latex'=>'C\\land(x>y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100100'=>array('name'=>'and less','latex'=>'C\\land(x<y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100101'=>array('name'=>'and greater or equal','latex'=>'C\\land(x\\ge y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100110'=>array('name'=>'and less or equal','latex'=>'C\\land(x\\le y)','descriptors'=>array('logical conjunction','condition chaining')),
            '100111'=>array('name'=>'or equal','latex'=>'C\\lor(x=y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101000'=>array('name'=>'or not equal','latex'=>'C\\lor(x\\ne y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101001'=>array('name'=>'or greater','latex'=>'C\\lor(x>y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101010'=>array('name'=>'or less','latex'=>'C\\lor(x<y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101011'=>array('name'=>'or greater or equal','latex'=>'C\\lor(x\\ge y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101100'=>array('name'=>'or less or equal','latex'=>'C\\lor(x\\le y)','descriptors'=>array('logical disjunction','condition chaining')),
            '101110'=>array('name'=>'xor equal','latex'=>'C\\oplus(x=y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '101111'=>array('name'=>'xor not equal','latex'=>'C\\oplus(x\\ne y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '110000'=>array('name'=>'xor greater','latex'=>'C\\oplus(x>y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '110001'=>array('name'=>'xor less','latex'=>'C\\oplus(x<y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '110010'=>array('name'=>'xor greater or equal','latex'=>'C\\oplus(x\\ge y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '110011'=>array('name'=>'xor less or equal','latex'=>'C\\oplus(x\\le y)','descriptors'=>array('exclusive disjunction','condition chaining')),
            '110100'=>array('name'=>'factorial','latex'=>'n!','descriptors'=>array('discrete product','nonnegative integer')),
            '110101'=>array('name'=>'exponential','latex'=>'e^x','descriptors'=>array('exponential function','natural base')),
            '110110'=>array('name'=>'natural logarithm','latex'=>'\\ln(x)','descriptors'=>array('logarithmic function','positive domain')),
            '110111'=>array('name'=>'logarithm base y','latex'=>'\\log_y(x)','descriptors'=>array('logarithmic function','configurable base')),
            '111000'=>array('name'=>'integrand cell','latex'=>'A\\approx w\\,y\\,h','descriptors'=>array('integration primitive','geometric cell')),
            '111001'=>array('name'=>'integral','latex'=>'\\int_a^b f(x)\\,dx','descriptors'=>array('calculus operator','accumulation')),
            '111010'=>array('name'=>'find integral','latex'=>'\\sum_i w_i y_i h_i','descriptors'=>array('numerical integration','cell accumulation')),
            '111011'=>array('name'=>'conditional probability','latex'=>'P(B\\mid A)=\\frac{P(A\\cap B)}{P(A)}','descriptors'=>array('conditional probability','requires condition')),
            '111100'=>array('name'=>'bayes probability','latex'=>'P(A\\mid B)=\\frac{P(B\\mid A)P(A)}{P(B)}','descriptors'=>array('bayes theorem','conditional probability')),
        );
    }

    public static function describe($opcode)
    {
        $opcode = trim((string)$opcode);
        $table = self::table();
        if (!isset($table[$opcode])) return null;
        return array('opcode'=>$opcode) + $table[$opcode];
    }

    public static function all()
    {
        $out = array();
        foreach (self::table() as $opcode=>$meta) $out[] = array('opcode'=>$opcode) + $meta;
        return $out;
    }
}
