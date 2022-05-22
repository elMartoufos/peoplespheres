<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailMakerController extends Controller
{
    private $_SEPARATOR = ["-", " ", "_", "."];
    
    public function newEmail(Request $request)
    {
        $input = $request->input();
        if (!isset($input['at']) || $input['at'] !== "@") {
            echo json_encode(["error" => 1, "msg" => "at arg missing"]);
            exit;
        }
        $email = "";
        foreach ($input as $data) {
            if (!is_array($data) && $data === "@") {
                $email .= $data;
                continue;
            }
            if (!empty($data["expression"])) {
                $input = $this->executeExpression($data["expression"], $data["value"]);
            } else {
                $input = $data["value"];
            }
            $email .= !empty($email) && substr($email, -1) !== "@" ? "." . $input : $input;
        }
        
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $email = strtolower($email);
            echo json_encode(["data" => ["id" => $email, "value" => $email]]);
        }else{
            echo json_encode(["error" => 1, "msg" => "bad input"]);
        }
    }

    public function token()
    {
        return csrf_token(); 
    }
    
    public function executeExpression($expression, $value)
    {
        if ($this->checkAvaibleExpression($expression)) {
            return $this->$expression($value);
        } else {
            echo json_encode(["error" => 1, "msg" => "unkown expression => $expression"]);
            exit;
        }
    }

    private function checkAvaibleExpression($expression)
    {
        return method_exists($this, $expression);
    }

    private function eachWordFirstChars($value)
    {
        $allWords = $this->explodeValue($value);
        $result = "";
        foreach ($allWords as $word) {
            $result .= $this->getFirstChar($word);
        }
        return $result;
    }

    private function explodeValue($value)
    {
        return explode(" ", str_replace($this->_SEPARATOR, " ", $value));
    }

    private function getFirstChar($word)
    {
        return substr($word, 0, 1);
    }

    private function lastWord($value)
    {
        $allWords = $this->explodeValue($value);
        return $allWords[count($allWords) - 1];
    }
}
