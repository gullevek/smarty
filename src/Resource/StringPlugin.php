<?php
/**
 * Smarty Internal Plugin Resource String
 *
 * @package    Smarty
 * @subpackage TemplateResources
 * @author     Uwe Tews
 * @author     Rodney Rehm
 */

namespace Smarty\Resource;
use Smarty\Smarty;
use Smarty\Template;
use Smarty\Template\Source;

/**
 * Smarty Internal Plugin Resource String
 * Implements the strings as resource for Smarty template
 * {@internal unlike eval-resources the compiled state of string-resources is saved for subsequent access}}
 *
 * @package    Smarty
 * @subpackage TemplateResources
 */
class StringPlugin extends BasePlugin {

	/**
	 * populate Source Object with meta data from Resource
	 *
	 * @param Source $source source object
	 * @param Template $_template template object
	 *
	 * @return void
	 */
	public function populate(Source $source, Template $_template = null) {
		$source->uid = $source->filepath = sha1($source->name . $source->smarty->_joined_template_dir);
		$source->timestamp = $source->exists = true;
	}

	/**
	 * Load template's source from $resource_name into current template object
	 *
	 * @param Source $source source object
	 *
	 * @return string                 template source
	 * @uses decode() to decode base64 and urlencoded template_resources
	 *
	 */
	public function getContent(Source $source) {
		return $this->decode($source->name);
	}

	/**
	 * decode base64 and urlencode
	 *
	 * @param string $string template_resource to decode
	 *
	 * @return string decoded template_resource
	 */
	protected function decode($string) {
		// decode if specified
		if (($pos = strpos($string, ':')) !== false) {
			if (!strncmp($string, 'base64', 6)) {
				return base64_decode(substr($string, 7));
			} elseif (!strncmp($string, 'urlencode', 9)) {
				return urldecode(substr($string, 10));
			}
		}
		return $string;
	}

	/**
	 * modify resource_name according to resource handlers specifications
	 *
	 * @param Smarty $smarty Smarty instance
	 * @param string $resource_name resource_name to make unique
	 * @param boolean $isConfig flag for config resource
	 *
	 * @return string unique resource name
	 */
	public function buildUniqueResourceName(Smarty $smarty, $resource_name, $isConfig = false) {
		return get_class($this) . '#' . $this->decode($resource_name);
	}

	/**
	 * Determine basename for compiled filename
	 * Always returns an empty string.
	 *
	 * @param Source $source source object
	 *
	 * @return string                 resource's basename
	 */
	public function getBasename(Source $source) {
		return '';
	}

	/*
		* Disable timestamp checks for string resource.
		*
		* @return bool
		*/
	/**
	 * @return bool
	 */
	public function checkTimestamps() {
		return false;
	}
}