<?php
/**
 * @Author: Jakub Hrášek
 * @Date:   2016-11-21 11:21:53
 * @Last Modified by:   Jakub Hrášek
 * @Last Modified time: 2016-12-06 09:04:41
 */

namespace hrasekj\api;


use Yii;
use yii\base\BootstrapInterface;
use yii\base\Exception;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\web\HttpException;
use yii\web\Response;

class Module extends \yii\base\Module implements BootstrapInterface
{
	/**
	 * @var string when user login session expires. Using strtotime.
	 * @see http://php.net/manual/en/function.strtotime.php
	 */
	public $expirationTime = '+60 minutes';

	/**
	 * @var string the namespace that model classes are in.
	 */
	public $modelNamespace;

	/**
	 * @var boolean if you want to use your own rules, se this to true
	 */
	public $customRules = false;


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
		// default url rules
		if ($this->customRules === false) {
			$app->getUrlManager()->addRules([
				'GET,HEAD /'.$this->id.'/<controller>/' => $this->id.'/<controller>/index',
				'GET,HEAD /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/view',
				'POST /'.$this->id.'/<controller>/' => $this->id.'/<controller>/create',
				'PUT,PATCH /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/update',
				'DELETE /'.$this->id.'/<controller>/<id:\d+>' => $this->id.'/<controller>/delete',
			]);
		}

		// api error handling
		$app->getResponse()->on('beforeSend', [$this, 'responseBeforeSend']);
	}


	/**
	 * Response Before send event. For handling errors.
	 */
	public function responseBeforeSend($event)
	{
		$errorHandler = Yii::$app->getErrorHandler();
		$response = $event->sender;
		$request = Yii::$app->getRequest();

		$needle = $this->id.'/';
		$haystack = $request->getPathInfo();

		if ($errorHandler->exception !== null && substr($haystack, 0, strlen($needle)) === $needle) {
			$response->format = Response::FORMAT_JSON;
			$response->data = $this->convertExceptionToArray($errorHandler->exception);

			if ($errorHandler->exception instanceof HttpException) {
				$response->statusCode = $errorHandler->exception->statusCode;
			}
			else {
				$response->statusCode = 500;
			}
		}
	}

	/**
	 * Converts an exception into an array.
	 * @param \Exception $exception the exception being converted
	 * @return array the array representation of the exception.
	 */
	protected function convertExceptionToArray($exception)
	{
		if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
			$exception = new HttpException(500, Yii::t('yii', 'An internal server error occurred.'));
		}

		$array = [
			'name' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
			'message' => $exception->getMessage(),
			'code' => $exception->getCode(),
		];
		if ($exception instanceof HttpException) {
			$array['status'] = $exception->statusCode;
		}
		if (YII_DEBUG) {
			$array['type'] = get_class($exception);
			if (!$exception instanceof UserException) {
				$array['file'] = $exception->getFile();
				$array['line'] = $exception->getLine();
				$array['stack-trace'] = explode("\n", $exception->getTraceAsString());
				if ($exception instanceof \yii\db\Exception) {
					$array['error-info'] = $exception->errorInfo;
				}
			}
		}
		if (($prev = $exception->getPrevious()) !== null) {
			$array['previous'] = $this->convertExceptionToArray($prev);
		}

		return $array;
	}

}
