<?php
namespace Smarty\Compile\Modifier;
/**
 * Smarty count_characters modifier plugin
 * Type:     modifier
 * Name:     count_characters
 * Purpose:  count the number of characters in a text
 *
 * @link   https://www.smarty.net/manual/en/language.modifier.count.characters.php count_characters (Smarty online
 *         manual)
 * @author Uwe Tews
 */

class CountCharactersModifierCompiler extends Base {

	public function compile($params, \Smarty\Compiler\Template $compiler) {
		if (!isset($params[ 1 ]) || $params[ 1 ] !== 'true') {
			return 'preg_match_all(\'/[^\s]/' . \Smarty\Smarty::$_UTF8_MODIFIER . '\',' . $params[ 0 ] . ', $tmp)';
		}
		return 'mb_strlen(' . $params[ 0 ] . ', \'' . addslashes(\Smarty\Smarty::$_CHARSET) . '\')';
	}

}