<?php

/**
 * Google Analytics statistics class.
 *
 * @see https://code.google.com/apis/console#access
 *
 * Step 1 (optional):
 * Create a new Google API project. Enable Analytics API.
 *
 * Step 2 (optional):
 * Create an OAUTH 2.0 client ID. Fill in the required information.
 * Application type: Installed application
 * Installed application type: Other
 *
 * Step 3 (optional):
 * Add Client ID and Client secret to the main configuration file:
 *	...
 *	'ycm'=>array(
 *		...
 *		'analytics'=>array(
 *			'clientId'=>'YOUR_CLIENT_ID',
 *			'clientSecret'=>'YOUR_CLIENT_SECRET',
 *		),
 *	),
 *	...
 * You can use the same ID and secret for all your applications.
 * You can also use the default Client ID and Client secret.
 *
 * Step 4:
 * Run setup and log in with the account details you want to track.
 *
 * Step 5:
 * Add Tracking ID, Profile ID and Access Token to the main configuration file:
 *	...
 *	'ycm'=>array(
 *		...
 *		'analytics'=>array(
 *			//'clientId'=>'YOUR_CLIENT_ID', // optional
 *			//'clientSecret'=>'YOUR_CLIENT_SECRET', // optional
 *			'trackingId'=>'UA-XXXXXX-X',
 *			'profileId'=>XXXXXX,
 *			'accessToken'=>'YOUR_ACCESS_TOKEN_STRING',
 *		),
 *	),
 *	...
 */

Yii::import('ycm.vendors.google.Google_Client', true);
Yii::import('ycm.vendors.google.contrib.Google_AnalyticsService', true);

class Stats extends CComponent
{
	public $client;
	protected $analytics;
	protected $clientId='644607768495.apps.googleusercontent.com';
	protected $clientSecret='00BEjgVVQ8GFLTQzVCZDEbto';
	protected $trackingId;
	protected $profileId;
	protected $accessToken;
	protected $startDate;
	protected $endDate;

	/**
	 * Constructor.
	 * @param array $config
	 * @throws CHttpException
	 */
	public function __construct(array $config=array())
	{
		foreach ($config as $property=>$value) {
			$this->$property=$value;
		}

		$this->client=new Google_Client();
		$this->client->setClientId($this->clientId);
		$this->client->setClientSecret($this->clientSecret);
		$this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
		$this->client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
		$this->client->setUseObjects(true);

		try {
			$this->analytics=new Google_AnalyticsService($this->client);
		} catch (exception $e) { // Google_ServiceException doesn't cover all Exceptions.
			throw new CHttpException(500,Yii::t(
				'YcmModule.ycm',
				'There was an Google Analytics API service error {code}: {message}',
				array('{code}'=>$e->getCode(),'{message}'=>$e->getMessage())
			));
		}

		if ($this->accessToken!==null) {
			$this->client->setAccessToken($this->accessToken);
		}
	}

	/**
	 * Authenticate with Auth code.
	 * @param null $authCode
	 * @return mixed
	 * @throws CHttpException
	 */
	public function authenticate($authCode=null) {
		try {
			$accessToken=$this->client->authenticate($authCode);
			return $accessToken;
		} catch (Exception $e) {
			throw new CHttpException(500,Yii::t(
				'YcmModule.ycm',
				'Unable to authenticate with Auth code {code}: {message}',
				array('{code}'=>$e->getCode(),'{message}'=>$e->getMessage())
			));
		}
	}

	/**
	 * Get one Google Analytics profile.
	 * @param null $accountId
	 * @param null $trackingId
	 * @return array|bool
	 * @throws CHttpException
	 */
	public function getProfile($accountId=null,$trackingId=null)
	{
		if ($trackingId===null) {
			$trackingId=$this->trackingId;
		}
		if ($accountId===null) {
			list($pre,$accountId,$post)=explode('-',$trackingId);
		}
		try {
			$results=$this->analytics->management_profiles->listManagementProfiles($accountId,$trackingId);
			if (count($results->items)>0) {
				$profile=array(
					'profileId'=>$results->items[0]->id,
					'trackingId'=>$trackingId,
					'name'=>str_replace('http://','',$results->items[0]->websiteUrl),
				);
				return $profile;
			}
		} catch (Google_ServiceException $e) {
			throw new CHttpException(500,Yii::t(
				'YcmModule.ycm',
				'There was an Google Analytics API service error {code}: {message}',
				array('{code}'=>$e->getCode(),'{message}'=>$e->getMessage())
			));
		}
		return false;
	}

	/**
	 * Get all Google Analytics profiles.
	 * @return array|bool
	 * @throws CHttpException
	 */
	public function getAllProfiles()
	{
		try {
			$results=array();
			$accounts=$this->analytics->management_accounts->listManagementAccounts();
			if (count($accounts->items)>0) {
				foreach ($accounts->items as $accountItem) {
					$properties=$this->analytics->management_webproperties->listManagementWebproperties($accountItem->id);
					if (count($properties->items)>0) {
						foreach ($properties->items as $propertyItem) {
							$profile=$this->getProfile($accountItem->id,$propertyItem->id);
							if ($profile) {
								array_push($results,$profile);
							}
						}
					}
				}
				return $results;
			}
		} catch (Google_ServiceException $e) {
			throw new CHttpException(500,Yii::t(
				'YcmModule.ycm',
				'There was an Google Analytics API service error {code}: {message}',
				array('{code}'=>$e->getCode(),'{message}'=>$e->getMessage())
			));
		}
		return false;
	}

	/**
	 * Get Google Analytics data.
	 * @param $metric
	 * @param array $params
	 * @return Google_GaData
	 * @throws CHttpException
	 */
	protected function getData($metric,$params=array())
	{
		try {
			if (Yii::app()->cache) {
				$cacheID='ycm-'.md5($this->profileId.$this->startDate.$this->endDate.$metric.implode(',',$params));
				$data=Yii::app()->cache->get($cacheID);
				if ($data===false) {
					$data=$this->analytics->data_ga->get(
						'ga:'.$this->profileId,
						$this->startDate,
						$this->endDate,
						$metric,
						$params
					);
					Yii::app()->cache->set($cacheID,$data,1800);
				}
				return $data;
			} else {
				return $this->analytics->data_ga->get(
					'ga:'.$this->profileId,
					$this->startDate,
					$this->endDate,
					$metric,
					$params
				);
			}
		} catch (exception $e){ // Google_ServiceException doesn't cover all Exceptions.
			throw new CHttpException(500,Yii::t(
				'YcmModule.ycm',
				'There was an Google Analytics API service error {code}: {message}',
				array('{code}'=>$e->getCode(),'{message}'=>$e->getMessage())
			));
		}
	}

	/**
	 * Get daily device traffic.
	 * @return array
	 */
	public function getDeviceData()
	{
		$deviceData=array();
		$tmpData=array();
		$tablet=array();
		$mobile=array();

		$params=array(
			'segment'=>'dynamic::ga:isMobile==No',
			'dimensions'=>'ga:date',
			'sort'=>'ga:date',
		);
		$result=$this->getData('ga:pageviews',$params);
		foreach ($result->rows as $item) {
			// Add desktop
			$tmpData[date('Y-m-d',strtotime($item[0]))][]=(int)$item[1];
		}

		$params=array(
			'segment'=>'gaid::-13',
			'dimensions'=>'ga:date',
			'sort'=>'ga:date',
		);
		$result=$this->getData('ga:pageviews',$params);
		foreach ($result->rows as $key=>$item) {
			$data=array(
				date('Y-m-d',strtotime($item[0])),
				(int)$item[1],
			);
			$tablet[$key]=$data;
			// Add tablet
			$tmpData[date('Y-m-d',strtotime($item[0]))][]=(int)$item[1];
		}

		$params=array(
			'segment'=>'gaid::-11',
			'dimensions'=>'ga:date',
			'sort'=>'ga:date',
		);
		$result=$this->getData('ga:pageviews',$params);
		foreach ($result->rows as $key=>$item) {
			$data=array(
				date('Y-m-d',strtotime($item[0])),
				(int)$item[1],
			);
			$mobile[$key]=$data;
		}

		foreach ($mobile as $key=>$item) {
			// Add smartphone
			$tmpData[$item[0]][]=(int)$item[1]-(int)$tablet[$key][1];
		}

		foreach ($tmpData as $key=>$item) {
			// Format array for JSON
			$data=(object)array(
				'date'=>$key,
				'a'=>(int)$item[0],
				'b'=>(int)$item[1],
				'c'=>(int)$item[2],
			);
			$deviceData[]=$data;
		}

		return $deviceData;
	}

	/**
	 * Get daily visitor data.
	 * @return array
	 */
	public function getVisitorData()
	{
		$visitorData=array();

		$params=array(
			'dimensions'=>'ga:date',
			'sort'=>'ga:date',
		);
		$result=$this->getData('ga:pageviews,ga:uniquePageviews,ga:visits,ga:visitors,ga:newVisits',$params);
		foreach ($result->rows as $item) {
			$data=(object)array(
				(string)'date'=>date('Y-m-d',strtotime($item[0])),
				'a'=>(int)$item[1],
				'b'=>(int)$item[2],
				'c'=>(int)$item[3],
				'd'=>(int)$item[4],
				'e'=>(int)$item[5],
			);
			$visitorData[]=$data;
		}

		return $visitorData;
	}

	/**
	 * Get traffic sources data.
	 * @return array
	 */
	public function getTrafficData()
	{
		$trafficData=array();

		$params=array(
			'dimensions'=>'ga:medium',
			'sort'=>'-ga:visits',
		);
		$result=$this->getData('ga:visits',$params);
		$total=$result->totalsForAllResults['ga:visits'];
		$searchTraffic=0;
		$searchType=array(
			'organic', // Organic search traffic
			'cpa', // Paid search traffic
			'cpc', // Paid search traffic
			'cpm', // Paid search traffic
			'cpp', // Paid search traffic
			'cpv', // Paid search traffic
			'ppc', // Paid search traffic
		);
		if ($total>0 && count($result->rows)>0) {
			foreach ($result->rows as $item) {
				if (in_array($item[0],$searchType) && $item[1]>0) {
					$searchTraffic+=(int)$item[1];
				}
				if ($item[0]=='referral' && $item[1]>0) {
					$data=(object)array(
						'label'=>Yii::t('YcmModule.ycm','Referral {percentage}',array('{percentage}'=>Yii::app()->numberFormatter->formatPercentage($item[1]/$total))),
						'value'=>(int)$item[1],
					);
					$trafficData[]=$data;
				}
				if ($item[0]=='(none)' && $item[1]>0) {
					$data=(object)array(
						'label'=>Yii::t('YcmModule.ycm','Direct {percentage}',array('{percentage}'=>Yii::app()->numberFormatter->formatPercentage($item[1]/$total))),
						'value'=>(int)$item[1],
					);
					$trafficData[]=$data;
				}
			}
			if ($searchTraffic>0) {
				$data=(object)array(
					'label'=>Yii::t('YcmModule.ycm','Search {percentage}',array('{percentage}'=>Yii::app()->numberFormatter->formatPercentage($searchTraffic/$total))),
					'value'=>(int)$searchTraffic,
				);
				$trafficData[]=$data;
			}
		}

		return $trafficData;
	}

	/**
	 * Get top keywords.
	 * @return mixed
	 */
	public function getKeywords()
	{
		$params=array(
			'dimensions'=>'ga:keyword',
			'sort'=>'-ga:visits',
			'filters'=>'ga:keyword!=(not set);ga:keyword!=(not provided)',
			'max-results'=>'10',
		);
		$result=$this->getData('ga:visits',$params);
		return $result->rows;
	}

	/**
	 * Get top referrers.
	 * @return mixed
	 */
	public function getReferrers()
	{
		$params=array(
			'dimensions'=>'ga:source',
			'sort'=>'-ga:visits',
			'filters'=>'ga:medium==referral',
			'max-results'=>'10',
		);
		$result=$this->getData('ga:visits',$params);
		return $result->rows;
	}

	/**
	 * Get top pages.
	 * @return mixed
	 */
	public function getPages()
	{
		$params=array(
			'dimensions'=>'ga:hostname,ga:pagePath,ga:pageTitle',
			'sort'=>'-ga:pageviews',
			'max-results'=>'10',
		);
		$result=$this->getData('ga:pageviews,ga:uniquePageviews,ga:avgTimeOnPage',$params);
		return $result;
	}

	/**
	 * Get overview data.
	 * @return mixed
	 */
	public function getUsage()
	{
		$metrics='ga:pageviews,ga:uniquePageviews,ga:visits,ga:visitors,ga:newVisits,ga:bounces,ga:entrances,ga:timeOnSite,ga:timeOnPage,ga:exits';
		$result=$this->getData($metrics);
		return $result->totalsForAllResults;
	}
}