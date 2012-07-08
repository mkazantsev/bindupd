<?php

class StringTokenizer {
    
    private /* array */ $tokens;

	private /* index */ $index;
    
    /**
     * Constructs a string tokenizer for the specified string
     *
     */
    public function __construct(/* string */ $str,
    	/* string */ $delim = " \n\r\f\t") {
        $tok = strtok($str, $delim);
		while ($tok) {
			$this->tokens[] = $tok;
			$tok = strtok($delim);
		}
		$this->index = 0;
    }

    public function countTokens() {
	    return count($this->tokens);
    }

    /**
     * Checks if there are more tokens available from this tokenizer's string.
     *
     */
    public function hasMoreTokens() {
        return ($this->countTokens() != $this->index);
    }

    /**
     * Returns the next token from this string tokenizer
	 *
     */
    public function nextToken() {
    	if ($this->index == $this->countTokens())
    		return false;
        $res = $this->tokens[$this->index];
        $this->index++;      
        return $res;
    }
}
?>