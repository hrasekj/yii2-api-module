<?php
/**
 * @Author: Jakub Hrášek
 * @Date:   2016-11-21 11:21:53
 * @Last Modified by:   Jakub Hrášek
 * @Last Modified time: 2016-11-26 15:51:15
 */

namespace api;


use Yii;
use yii\base\BootstrapInterface;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\Response;

class Module extends \yii\base\Module implements BootstrapInterface
{
	/**
	 * @var string the namespace that model classes are in.
	 */
	public $modelNamespace;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->modelNamespace === null) {
			throw new InvalidConfigException('The "modelNamespace" property must be set.');
		}
	}


	/**
	 * @inheritdoc
	 */
	public function bootstrap($app)
	{
		// urlManager rules
		$app->getUrlManager()->addRules([
			'GET /'.$this->id.'/<controller>/' => $this->id.'/<controller>/index',
			'GET /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/view',
			'POST /'.$this->id.'/<controller>/' => $this->id.'/<controller>/create',
			'PUT,PATCH /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/update',
			'DELETE /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/delete',
		]);

		$app->getResponse()->on('beforeSend', [$this, 'responseBeforeSend']);
	}


	/**
	 * Response Before send event. For handling errors.
	 */
	public function responseBeforeSend($event)
	{
		$errorHandler = Yii::$app->getErrorHandler();
		$response = $event->sender;

		if ($errorHandler->exception !== null) {
			$response->format = Response::FORMAT_JSON;

			if ($errorHandler->exception instanceof HttpException) {
				$data = [
					'name' => $errorHandler->exception->getName(),
					'message' => $errorHandler->exception->getMessage(),
					'status' => ($statusCode = $errorHandler->exception->statusCode),
					'code' => $errorHandler->exception->getCode(),
				];
			}
			elseif ($errorHandler->exception instanceof Exception) {
				$data = [
					'name' => $errorHandler->exception->getName(),
					'message' => $errorHandler->exception->getMessage(),
					'status' => ($statusCode = 500),
					'code' => $errorHandler->exception->getCode(),
				];
			}
			else {
				$data = [
					'name' => "Fatal Error",
					'message' => $errorHandler->exception->getMessage(),
					'status' => ($statusCode = 500),
					'code' => $errorHandler->exception->getCode(),
				];
			}

			$response->data = $data;
			$response->statusCode = $statusCode;
		}
	}

}
