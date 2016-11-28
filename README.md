# Yii2 REST API module

## Instalace
Do souboru composer.json vložíme repository (jelikož jsem byl líný registrovat to do Packagist). A přidáme do `require`.
```json
{
	"require": {
	    "hrasekj/yii2-api-module": "dev-master"
	},
	"repositories": [
	    {
	        "type": "vcs",
	        "url": "https://github.com/hrasekj/yii2-api-module"
	    }
	]
}
```

V konfigu aplikace pak přidáme nový module a nasatavíme pro bootstraping (kvůli registraci rout modulu).
```php
$config = [
	'bootstrap' => ['api'],
	'modules' => [
		'api' => [
	        'class' => 'api\Module',
	        'controllerNamespace' => 'frontend\controllers\api',
	        'modelNamespace' => 'common\models',
	        'controllerMap' => [
	            /*'user' => [
	                'class' => 'api\controllers\DefaultController',
	                'modelClass' => 'common\models\User'
	            ]*/
	            'user' => 'api\controllers\DefaultController',
	        ]
	    ]
	]
];
```

## Použití
Jakmile je module nastaven a vytvořena mapa kontrolerů je již možné API plně využívat.

Řekněme, že chceme vytvořit nového uživatele. V mapě definujeme `user` kontroler a nasměrujeme jej na `api\controllers\DefaultController`. Pokud budeme chtít získat všechny uživatele z databáze.
```
GET /user

// Response
{
  "items": [
    {
      "userID": 1,
      "name": "admin",
    }
  ],
  "_links": {
    "self": {
      "href": "http://example.com/api/user?page=1"
    }
  },
  "_meta": {
    "totalCount": 5,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```
Jelikož serializer je nastaven na [collectionEnvelope](http://www.yiiframework.com/doc-2.0/yii-rest-serializer.html#$collectionEnvelope-detail) `items`. Uživatelé tak budou zabaleni v property `items`. Pokud bychom `collectionEnvelope` nenastavili na výstupu bude pouze pole uživatelů a meta informace budou v hlavičkách Response.

## Nastavení Module
API module má několik povinných nastavení. Jsou to tyto:

**controllerNamespace** - namespace pro vlastní kontrolery. Pokud vytvoříme vlastní kontroler. Musí mít namespace definovaný v tomto parametru. Vlastní kontroler musí rozšiřovat třídu `api\controllers\ActiveController` nebo  `api\controllers\Controller`.

**modelNamespace** - namespace umístění modelů. Pokud není ve třídě DefaultController nastavena property `modelClass`, je tato property vytvořena automaticky z `modelNamespace` a id kontroleru.

**controllerMap** - mapa kontrolerů. Mapa kontrolerů je povinná, jelikož jsem nepřišel na způsob jak v routách směrovat z neexistujícího kontroleru na DefaultController.

## api\controllers\DefaultController options
- `modelClass` - namespace modelu
- `updateScenario` - scenario pro update
- `createScenario` - scenario pro create
- `serializer` - config pro data [serializer](http://www.yiiframework.com/doc-2.0/yii-rest-serializer.html)
