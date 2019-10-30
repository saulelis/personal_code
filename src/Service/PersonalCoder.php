<?php
namespace App\Service;

class PersonalCoder
{
   
    /**
     * Generates a personal code.
     * @param string $dob Date of birth
     * @param string $gender_input Gender code
     * @return array A collection of codes
     */
    public function generateCode(string $dob, string $gender_input)
    {
        if (!$this->validateInput($dob, $gender_input)) {
            return [];
        }
        
        $gender_code = $this->getRealGenderCode($dob, $gender_input);
        
        $codes = [];
        $date_segment = date('ymd', strtotime($dob));
        for ($i = 0; $i < 1000; $i++) {
            $index_number = sprintf('%03d', $i);
            $aj_code = $gender_code . $date_segment . $index_number;
            $control_number = $this->getControlNumber($aj_code);
            $codes[] = $aj_code . $control_number;
        }

        return $codes;
    }
    
    /**
     * @param string $dob Date of birth in strtotime-valid format
     * @param string $gender_input Gender code
     * @return int A true gender code, accounting for century of birth
     */
    private function getRealGenderCode($dob, $gender_input)
    {
        // Biological sex (1 for male, 2 for female) implied from input.
        $sex = $gender_input % 2 ? 1 : 2;
        // This evals to 0 for the 19th century, 2 for 20th, 4 for 21st.
        $century_modifier = (strftime('%C', strtotime($dob)) - 18) * 2;
        $result = $sex + $century_modifier;
        
        return $result;        
    }
    
    /**
     * Generates a new number or extracts one from a full personal code.
     * @param int $code The personal code (with or without the control number)
     * @param int $pass Indicator for recursion if the first pass 
     *    fails to yield a valid number
     * @return int
     */
    private function getControlNumber($code, $pass = 0)
    {
        if ($pass > 1) {
            // This is a third pass, so we know our number right away.
            return 0;
        }

        $sum = 0;
        // For the second pass we start with multiplying by 3.
        $multiplier = $pass ? 3 : 1;
        for ($i = 0; $i < 10; $i++) {
            $sum += $code[$i] * $multiplier;
            $multiplier++;
            // Reset the multiplier during the second pass.
            if ($multiplier > 9) {
                $multiplier = 1;
            }
        }
        
        $output = $sum % 11;
        if ($output == 10) {
            // Start over for the second or third pass.
            $output = $this->getControlNumber($code, $pass + 1);
        }
        
        return $output;
    }
    
    /**
     * @param string $code
     * @return bool
     */
    public function validateCode(string $code)
    {
        if (!is_numeric($code)) {
            return false;
        }
        
        // Typecasting to integer here takes care of a floating point 
        // that would slip through the is_numeric() check.
        if (strlen((int) $code) !== 11) {
            return false;
        }
        
        if ($code[0] == 9) {
            // There's an "anything goes" exception for codes starting with 9.
            return true;
        }
        
        if ($code[0] < 1 || $code[0] > 6) {
            return false;
        }
        
        // There's also an exception for elderly people who don't remember 
        // their birthday, but it is somewhat loosely defined, so I skip it.
        
        $control_number = $this->getControlNumber($code);
        if ($code[10] != $control_number) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validates API client input.
     * @param string $dob
     * @param int $gender
     * @return bool
     */
    private function validateInput($dob, $gender)
    {
        // Inputs like "-20191031" produce hardly predictable results.
        if ((int) $dob < 0) {
            return false;
        }
        
        $time = strtotime($dob);
        // Invalid date or date before 19th century.
        if ($time === false || $time < -5364668389) {
            return false;
        }
        
        // strlen() takes care of floating point values.
        if (!is_numeric($gender) || strlen($gender) > 1) {
            return false;
        }
        
        if ($gender < 1 || $gender > 6) {
            return false;
        }
        
        return true;
    }
}
