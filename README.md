# Instagram CakePHP Component #

The Instagram CakePHP component is a lightweight wrapper for the Instagram api, including basic requests to the Instagram [endpoints](http://instagram.com/developer/endpoints/) as well as the [realtime](http://instagram.com/developer/realtime/) api.

## Setup ##

You can add the repository as a git submodule to your Plugin directory after which it can be loaded as a component. For authentication the client id and the secret need to configured.

```
Configure::write('Instagram.client', 'CLIENT_ID');
Configure::write('Instagram.secret', 'SECRET');		
```

To make use of the realtime api we also need the callback url, this is where Instagram will do its requests to and you can process them

```
Configure::write('Instagram.callback_url', 'CALLBACK_URL');
```

## Example use ##

Recent media of the tag 'spring': ```$this->Instagram->get('/tags/spring/media/recent');```

Subscriptions to the realtime api: ```$this->Instagram->subscriptions();```

Add a subscription to the tag spring can be made like ```$this->Instagram->subscribe('tag', 'media', array('object_id' => 'spring'))```, this will call your callback url to check if a server is responding, by putting in ```$this->Instagram->verify()``` you can get your data as well as process the initial poll by Instagram.

Unsubscribing is done in a similiar fashion ```$this->Instagram->unsubscribe($subscription['id'])```

License
-----

	Copyright 2013 Joris Blaak

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.