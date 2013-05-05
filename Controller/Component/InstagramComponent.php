<?php
App::uses('HttpSocket', 'Network/Http');
/**   
* Instagram Component
* @author Joris Blaak <joris@label305.com>
* @require CakePHP 2.x
* @license THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT 
* WARRANTY OF ANY KIND. ONLY OUR CLIENTS FOR CUSTOM SOFTWARE 
* ARE ENTITLED TO A LIMITED WARRANTY UP TO SIX WEEKS AFTER 
* COMPLETION OR DEPLOYMENT. SEE OUR ARTICLE 5 OF OUR GENERAL  
* TERMS AND CONDITIONS FOR MORE INFORMATION ON OUR WARRANTY. 
*/
class InstagramComponent extends Component {

	/**
	 * Base url of the instagram api
	 * @var string
	 */
	public $apiBase = 'https://api.instagram.com/v1';

	/**
	 * Meta data of the last request, for information purposes
	 * @var array
	 */
	public $meta;

	/**
	 * Cake component initialize
	 * @param  Controller $controller 
	 */
	public function initialize(Controller $controller) {
        $this->controller = $controller;
    }
	
	/**
	 * List subscriptions
	 * @return array
	 */
	public function subscriptions() {
		return $this->get('/subscriptions/');
	}

	/**
	 * Subscribe to the realtime api
	 *
	 * Available objects:
	 *
	 * + Users
	 * + Tags
	 * + Locations
	 * + Geographies
	 *
	 * Available aspects:
	 *
	 * + Media
	 *
	 * Extra parameters:
	 *
	 * + object_id (in case of, for example, tags you can pass the tag here)
	 * + ...
	 * 
	 * @param  string $object    object you'd like to subscribe to
	 * @param  string $aspect    aspect of the object you'd like to subscribe to
	 * @param  array  $options   the different params available for the objects
	 * @return array             the subscription
	 */
	public function subscribe($object, $aspect, $options=array()) {
		$pass = array_merge(array(
			'object' => $object,
			'aspect' => $aspect
		), $options);
		return $this->post('/subscriptions/', $pass);
	}

	/**
	 * Unsubscribe from a subscription id
	 * @param  integer $id 
	 * @return boolean     
	 */
	public function unsubscribe($id) {
		return $this->delete('/subscriptions', array('id' => $id));
	}

	/**
	 * Verifies the request, also will handle the 'hub_challenge' in an uber ugly, but working, way
	 * @return mixed    boolean on error, otherwise input data
	 */
	public function verify() {
		if(isset($this->controller->request->query['hub_challenge'])) {
			echo $this->controller->request->query['hub_challenge'];
			die();//This is the ugly bit, we just 
		} else {
			$input = file_get_contents("php://input");
			if(hash_hmac('sha1', $input, Configure::read('Instagram.secret')) == $this->controller->request->header('X-Hub-Signature')) {
				return $this->process($input);	
			}
		}
		return false;
	}

	/**
	 * Make a get request to the api
	 * @param  string $url  relative to the base
	 * @param  array $data 
	 * @return array
	 */
	public function get($url, $data = array()) {
		$http = new HttpSocket();
		return $this->process($http->get($this->apiBase.$url, $this->_params($data)));
	}

	/**
	 * Make a post request to the api
	 * @param  string $url  relative to the base
	 * @param  array $data 
	 * @return array
	 */
	public function post($url, $data = array()) {
		$http = new HttpSocket();
		return $this->process($http->post($this->apiBase.$url, $this->_params($data)));
	}

	/**
	 * Make a delete request to the api
	 * @param  string $url  relative to the base
	 * @param  array $data 
	 * @return array
	 */
	public function delete($url, $data = array()) {
		$http = new HttpSocket();
		return $this->process($http->delete($this->apiBase.$url.'?'.http_build_query($this->_params($data))));
	}

	/**
	 * Wrapper for json_decode to array supressing php errors with invalid formatting
	 * @param  string $raw 	json input
	 * @return mixed      	array with result or true on success otherwise false
	 */
	private function process($raw) {
		$result = false;
		$data = @json_decode($raw, true);
		if(is_array($data)) {
			if(isset($data['data'], $data['meta']['code']) && $data['meta']['code'] == 200) {
				$result = $data['data'];
			} elseif(isset($data['meta']['code']) && $data['meta']['code'] == 200) {
				$result = true;
			}
			if(isset($data['meta'])) {
				$this->meta = $data['meta'];
			}
		}
		return $result;
	}

	/**
	 * Concatenates input data with required api data
	 * @param  array $data 
	 * @return array
	 */
	private function _params($data) {
		return array_merge(
			array(
				'client_id' => Configure::read('Instagram.client'),
				'client_secret' => Configure::read('Instagram.secret'),
				'callback_url' => Configure::read('Instagram.callback_url')
			), $data);
	}
}