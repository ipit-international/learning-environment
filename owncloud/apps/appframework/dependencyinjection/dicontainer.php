<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\AppFramework\DependencyInjection;

use OCA\AppFramework\Http\Http;
use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\Dispatcher;
use OCA\AppFramework\Core\API;
use OCA\AppFramework\Middleware\MiddlewareDispatcher;
use OCA\AppFramework\Middleware\Http\HttpMiddleware;
use OCA\AppFramework\Middleware\Security\SecurityMiddleware;
use OCA\AppFramework\Middleware\Twig\TwigMiddleware;
use OCA\AppFramework\Utility\FaviconFetcher;
use OCA\AppFramework\Utility\SimplePieAPIFactory;
use OCA\AppFramework\Utility\TimeFactory;

// register 3rdparty autoloaders
require_once __DIR__ . '/../3rdparty/SimplePie/autoloader.php';
// in OC6 pimple is already loaded
if(!class_exists('Pimple')) {
	require_once __DIR__ . '/../3rdparty/Pimple/Pimple.php';
}
require_once __DIR__ . '/../3rdparty/Twig/lib/Twig/Autoloader.php';
\Twig_Autoloader::register();


/**
 * This class extends Pimple (http://pimple.sensiolabs.org/) for reusability
 * To use this class, extend your own container from this. Should you require it
 * you can overwrite the dependencies with your own classes by simply redefining
 * a dependency
 */
class DIContainer extends \Pimple {


	/**
	 * Put your class dependencies in here
	 * @param string $appName the name of the app
	 */
	public function __construct($appName){

		$this['AppName'] = $appName;

		$this['API'] = $this->share(function($c){
			return new API($c['AppName']);
		});

		/**
		 * Http
		 */
		$this['Request'] = $this->share(function($c) {
			$params = json_decode(file_get_contents('php://input'), true);
			$params = is_array($params) ? $params: array();

			return new Request(
				array(
					'get' => $_GET,
					'post' => $_POST,
					'files' => $_FILES,
					'server' => $_SERVER,
					'env' => $_ENV,
					'session' => $_SESSION,
					'cookies' => $_COOKIE,
					'method' => (isset($_SERVER) && isset($_SERVER['REQUEST_METHOD']))
							? $_SERVER['REQUEST_METHOD']
							: null,
					'params' => $params,
					'urlParams' => $c['urlParams']
				)
			);
		});

		$this['Protocol'] = $this->share(function($c){
			if(isset($_SERVER['SERVER_PROTOCOL'])) {
				return new Http($_SERVER, $_SERVER['SERVER_PROTOCOL']);
			} else {
				return new Http($_SERVER);
			}
		});

		$this['Dispatcher'] = $this->share(function($c) {
			return new Dispatcher($c['Protocol'], $c['MiddlewareDispatcher']);
		});


		/**
		 * Twig
		 */
		// use this to specify the template directory
		$this['TwigTemplateDirectory'] = null;

		// if you want to cache the template directory, add this path
		$this['TwigTemplateCacheDirectory'] = null;

		// enables the l10n function as trans() function in twig
		$this['TwigL10N'] = $this->share(function($c){
			$trans = $c['API']->getTrans();;
			return new \Twig_SimpleFunction('trans', function () use ($trans) {
				$args = func_get_args();
				$string = array_shift($args);
				return $trans->t($string, $args);
			});
		});

		// enables the linkToRoute function as url() function in twig
		$this['TwigLinkToRoute'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('url', function () use ($api) {
				return call_user_func_array(array($api, 'linkToRoute'), func_get_args());
			});
		});

		// enables the addScript function as script() function in twig
		$this['TwigAddScript'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('script', function () use ($api) {
				call_user_func_array(array($api, 'addScript'), func_get_args());
			});
		});

		// enables the addScript function as style() function in twig
		$this['TwigAddStyle'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('style', function () use ($api) {
				call_user_func_array(array($api, 'addStyle'), func_get_args());
			});
		});

		// enables the linkToAbsoluteRoute function as abs_url() function in twig
		$this['TwigLinkToAbsoluteRoute'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('abs_url', function () use ($api) {
				$url = call_user_func_array(array($api, 'linkToRoute'), func_get_args());
				return $api->getAbsoluteURL($url);
			});
		});

		// enables the linkTo function as link_to() function in twig
		$this['TwigLinkTo'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('link_to', function () use ($api) {
				return call_user_func_array(array($api, 'linkTo'), func_get_args());
			});
		});

		// enables the imagePath function as image_path() function in twig
		$this['TwigImagePath'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('image_path', function () use ($api) {
				return call_user_func_array(array($api, 'imagePath'), func_get_args());
			});
		});


		$this['TwigLoader'] = $this->share(function($c){
			return new \Twig_Loader_Filesystem($c['TwigTemplateDirectory']);
		});

		$this['Twig'] = $this->share(function($c){
			$loader = $c['TwigLoader'];
			if($c['TwigTemplateCacheDirectory'] !== null){
				$twig = new \Twig_Environment($loader, array(
					'cache' => $c['TwigTemplateCacheDirectory'],
					'autoescape' => true
				));
			} else {
				$twig = new \Twig_Environment($loader, array(
					'autoescape' => true
				));
			}
			$twig->addFunction($c['TwigAddScript']);
			$twig->addFunction($c['TwigAddStyle']);
			$twig->addFunction($c['TwigL10N']);
			$twig->addFunction($c['TwigImagePath']);
			$twig->addFunction($c['TwigLinkTo']);
			$twig->addFunction($c['TwigLinkToRoute']);
			$twig->addFunction($c['TwigLinkToAbsoluteRoute']);
			return $twig;
		});


		/**
		 * Middleware
		 */
		$this['SecurityMiddleware'] = $this->share(function($c){
			return new SecurityMiddleware($c['API'], $c['Request']);
		});

		$this['HttpMiddleware'] = $this->share(function($c){
			return new HttpMiddleware($c['API'], $c['Request']);
		});

		$this['TwigMiddleware'] = $this->share(function($c){
			return new TwigMiddleware($c['API'], $c['Twig']);
		});

		$this['MiddlewareDispatcher'] = $this->share(function($c){
			$dispatcher = new MiddlewareDispatcher();
			$dispatcher->registerMiddleware($c['HttpMiddleware']);
			$dispatcher->registerMiddleware($c['SecurityMiddleware']);

			// only add twigmiddleware if the user set the template directory
			if($c['TwigTemplateDirectory'] !== null){
				$dispatcher->registerMiddleware($c['TwigMiddleware']);
			}

			return $dispatcher;
		});


		/**
		 * Utilities
		 */
		$this['SimplePieAPIFactory'] = $this->share(function($c){
			return new SimplePieAPIFactory();
		});

		$this['FaviconFetcher'] = $this->share(function($c){
			return new FaviconFetcher($c['SimplePieAPIFactory']);
		});

		$this['TimeFactory'] = $this->share(function($c){
			return new TimeFactory();
		});


	}


}
