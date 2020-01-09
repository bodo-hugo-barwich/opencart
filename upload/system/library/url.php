<?php
/**
 * @package   OpenCart
 * @author    Daniel Kerr
 * @copyright Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license   https://opensource.org/licenses/GPL-3.0
 * @author    Daniel Kerr
 * @see       https://www.opencart.com
 */

/**
 * URL class.
 */
class Url {
	/** @var string */
	private $url;
	/** @var Controller[] */
	private $rewrite = array();

	protected $scheme = '';
	protected $scheme_ssl = "";
	protected $host = "";
	protected $host_ssl = "";
	protected $route_base = "";
	protected $route_base_ssl = "";

	/**
	 * Constructor.
	 *
	 * @param string $url
	 * @param string $ssl Depricated
	 */
	public function __construct($url) {
		$this->url = $url;

		$this->route_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . "/";
		$this->route_base_ssl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . "/";

		if (isset($url) && $url !== '') {
		    $arrscheme = array();

		    if (preg_match('/^([^:]+):/', $url, $arrscheme) && isset($arrscheme[1])) {
		        switch ($arrscheme[1]) {
		            case 'http':
		                $this->ssl = false;
	                    $this->setRouteBase($url, false);
		                break;

		            case 'https':
		                $this->ssl = true;
		                $this->setRouteBase($url, true);
		                break;

		        }  //switch ($arrscheme[1])
		    } //if (preg_match('/^([^:]):/', $url, $arrscheme) && isset($arrscheme[1]))
		}  //if(isset($url) && $url !== '')
	}

	public function setURLScheme($scheme, $ssl = false) {
	    if($ssl)
	        $this->scheme = $scheme;
        else
            $this->scheme_ssl = $scheme;

	}

	public function setURLHost($host, $ssl = false) {
	    if($ssl)
	        $this->host = $host;
        else
            $this->host_ssl = $host;

	}

	public function setRouteBase($routebase, $ssl = false) {
	    if($routebase[strlen($routebase) - 1] != '/')
	        $routebase .= '/';

        if($ssl)
            $this->route_base_ssl = $routebase;
        else
            $this->route_base = $routebase;

	}

	/**
	 *	Add a rewrite method to the URL system
	 *
	 * @param Controller $rewrite
	 *
	 * @return void
	 */
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}

	/**
	 * Generates a URL
	 *
	 * @param string        $route
	 * @param string|array	$args
	 * @param bool			$js
	 *
	 * @return string
	 */
	public function link($route, $args = '', $js = false) {
	    $url = "";

	    if ($this->ssl && $secure) {
	        if(!empty($this->scheme_ssl))
	            $url = $this->scheme_ssl;

	            if(!empty($this->host_ssl))
	            {
	                if(!empty($url))
	                    $url .= ":";

	                    $url .= "//" . $this->host_ssl;
	            }

	            if(!empty($this->route_base_ssl))
	                $url .= $this->route_base_ssl;
	                else
	                    $url .= "/";

	    } else {  //It is not a SSL URL
	        if(!empty($this->scheme))
	            $url = $this->scheme;

	            if(!empty($this->host))
	            {
	                if(!empty($url))
	                    $url .= ":";

	                    $url .= "//" . $this->host;
	            }

	            if(!empty($this->route_base))
	                $url .= $this->route_base;
	                else
	                    $url .= "/";

	    } //if ($this->ssl && $secure)

	    $url .= 'index.php?route=' . (string)$route;

		if ($args) {
			if (!$js) {
				$amp = '&amp;';
			} else {
				$amp = '&';
			}

			if (is_array($args)) {
				$url .= $amp . http_build_query($args, '', $amp);
			} else {
				$url .= str_replace('&', $amp, '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}
