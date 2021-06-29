<?php

namespace App\Http\Services;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Ring\Client\StreamHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler as MonologStreamHandler;
use Monolog\Handler\SyslogHandler as MonologSyslogHandler;

/**
 * The Google API Client
 * https://github.com/google/google-api-php-client
 */
class GooleClient
{
    const LIBVER = "2.5.0";
    const USER_AGENT_SUFFIX = "google-api-php-client/";
    const OAUTH2_REVOKE_URI = 'https://oauth2.googleapis.com/revoke';
    const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
    const API_BASE_PATH = 'https://www.googleapis.com';

    /**
     * @var GuzzleHttp\ClientInterface $http
     */
    private $http;

    /**
     * @var array access token
     */
    private $token;

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var Psr\Log\LoggerInterface $logger
     */
    private $logger;

    /** @var array $scopes */
    // Scopes requested by the client
    protected $requestedScopes = [];

    /**
     * Construct the Google Client.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = array_merge(
            [
                'application_name' => '',

                // Don't change these unless you're working against a special development
                // or testing environment.
                'base_path' => self::API_BASE_PATH,

                // https://developers.google.com/console
                'client_id' => '',
                'client_secret' => '',

                // Path to JSON credentials or an array representing those credentials
                // @see Google_Client::setAuthConfig
                'credentials' => null,
                // @see Google_Client::setScopes
                'scopes' => null,
                // Sets X-Goog-User-Project, which specifies a user project to bill
                // for access charges associated with the request
                'quota_project' => null,

                'redirect_uri' => null,
                'state' => null,

                // Simple API access key, also from the API console. Ensure you get
                // a Server key, and not a Browser key.
                'developer_key' => '',

                // For use with Google Cloud Platform
                // fetch the ApplicationDefaultCredentials, if applicable
                // @see https://developers.google.com/identity/protocols/application-default-credentials
                'use_application_default_credentials' => false,
                'signing_key' => null,
                'signing_algorithm' => null,
                'subject' => null,

                // Other OAuth2 parameters.
                'hd' => '',
                'prompt' => '',
                'openid.realm' => '',
                'include_granted_scopes' => null,
                'login_hint' => '',
                'request_visible_actions' => '',
                'access_type' => 'online',
                'approval_prompt' => 'auto',

                // Task Runner retry configuration
                // @see Google_Task_Runner
                'retry' => array(),
                'retry_map' => null,

                // cache config for downstream auth caching
                'cache_config' => [],

                // function to be called when an access token is fetched
                // follows the signature function ($cacheKey, $accessToken)
                'token_callback' => null,

                // Service class used in Google_Client::verifyIdToken.
                // Explicitly pass this in to avoid setting JWT::$leeway
                'jwt' => null,

                // Setting api_format_v2 will return more detailed error messages
                // from certain APIs.
                'api_format_v2' => false
            ],
            $config
        );

        if (!is_null($this->config['credentials'])) {
            $this->setAuthConfig($this->config['credentials']);
            unset($this->config['credentials']);
        }

        if (!is_null($this->config['scopes'])) {
            $this->setScopes($this->config['scopes']);
            unset($this->config['scopes']);
        }
    }

    /**
     * For backwards compatibility
     * alias for fetchAccessTokenWithAuthCode
     *
     * @param $code string code from accounts.google.com
     * @return array access token
     * @deprecated
     */
    public function authenticate($code)
    {
        return $this->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * For backwards compatibility
     * alias for fetchAccessTokenWithAssertion
     *
     * @return array access token
     * @deprecated
     */
    public function refreshTokenWithAssertion()
    {
        return $this->fetchAccessTokenWithAssertion();
    }

    /**
     * Fetches a fresh access token with a given assertion token.
     * @param ClientInterface $authHttp optional.
     * @return array access token
     */
    public function fetchAccessTokenWithAssertion(ClientInterface $authHttp = null)
    {
        if (!$this->isUsingApplicationDefaultCredentials()) {
            throw new \Exception(
                'set the JSON service account credentials using'
                . ' Google_Client::setAuthConfig or set the path to your JSON file'
                . ' with the "GOOGLE_APPLICATION_CREDENTIALS" environment variable'
                . ' and call Google_Client::useApplicationDefaultCredentials to'
                . ' refresh a token with assertion.'
            );
        }

        $this->getLogger()->log(
            'info',
            'OAuth2 access token refresh with Signed JWT assertion grants.'
        );

        $credentials = $this->createApplicationDefaultCredentials();

        $httpHandler = $this->build($authHttp);//$authHttp = null
        $creds = $credentials->fetchAuthToken($httpHandler);
        if ($creds && isset($creds['access_token'])) {
            $creds['created'] = time();
            $this->setAccessToken($creds);
        }
        return $creds;
    }

    public static function build(ClientInterface $client = null)
    {
        $client = new Client(['proxy' => config("firebase.httpProxy.proxy")]);
        return new Guzzle6HttpHandler($client);
    }

    /**
     * For backwards compatibility
     * alias for fetchAccessTokenWithRefreshToken
     *
     * @param string $refreshToken
     * @return array access token
     */
    public function refreshToken($refreshToken)
    {
        return $this->fetchAccessTokenWithRefreshToken($refreshToken);
    }

    /**
     * Fetches a fresh OAuth 2.0 access token with the given refresh token.
     * @param string $refreshToken
     * @return array access token
     */
    public function fetchAccessTokenWithRefreshToken($refreshToken = null)
    {
        if (null === $refreshToken) {
            if (!isset($this->token['refresh_token'])) {
                throw new \Exception(
                    'refresh token must be passed in or set as part of setAccessToken'
                );
            }
            $refreshToken = $this->token['refresh_token'];
        }
        $this->getLogger()->info('OAuth2 access token refresh');
        $auth = $this->getOAuth2Service();
        $auth->setRefreshToken($refreshToken);

        $httpHandler = HttpHandlerFactory::build($this->getHttpClient());
        $creds = $auth->fetchAuthToken($httpHandler);
        if ($creds && isset($creds['access_token'])) {
            $creds['created'] = time();
            if (!isset($creds['refresh_token'])) {
                $creds['refresh_token'] = $refreshToken;
            }
            $this->setAccessToken($creds);
        }

        return $creds;
    }

    /**
     * Create a URL to obtain user authorization.
     * The authorization endpoint allows the user to first
     * authenticate, and then grant/deny the access request.
     * @param string|array $scope The scope is expressed as an array or list of space-delimited strings.
     * @return string
     */
    public function createAuthUrl($scope = null)
    {
        if (empty($scope)) {
            $scope = $this->prepareScopes();
        }
        if (is_array($scope)) {
            $scope = implode(' ', $scope);
        }

        // only accept one of prompt or approval_prompt
        $approvalPrompt = $this->config['prompt']
            ? null
            : $this->config['approval_prompt'];

        // include_granted_scopes should be string "true", string "false", or null
        $includeGrantedScopes = $this->config['include_granted_scopes'] === null
            ? null
            : var_export($this->config['include_granted_scopes'], true);

        $params = array_filter(
            [
                'access_type' => $this->config['access_type'],
                'approval_prompt' => $approvalPrompt,
                'hd' => $this->config['hd'],
                'include_granted_scopes' => $includeGrantedScopes,
                'login_hint' => $this->config['login_hint'],
                'openid.realm' => $this->config['openid.realm'],
                'prompt' => $this->config['prompt'],
                'response_type' => 'code',
                'scope' => $scope,
                'state' => $this->config['state'],
                //'CURLOPT_PROXY'=>'http://10.113.8.116:10809'
            ]
        );

        // If the list of scopes contains plus.login, add request_visible_actions
        // to auth URL.
        $rva = $this->config['request_visible_actions'];
        if (strlen($rva) > 0 && false !== strpos($scope, 'plus.login')) {
            $params['request_visible_actions'] = $rva;
        }

        $auth = $this->getOAuth2Service();

        return (string)$auth->buildFullAuthorizationUri($params);
    }


    /**
     * Set the configuration to use application default credentials for
     * authentication
     *
     * @see https://developers.google.com/identity/protocols/application-default-credentials
     * @param boolean $useAppCreds
     */
    public function useApplicationDefaultCredentials($useAppCreds = true)
    {
        $this->config['use_application_default_credentials'] = $useAppCreds;
    }

    /**
     * To prevent useApplicationDefaultCredentials from inappropriately being
     * called in a conditional
     *
     * @see https://developers.google.com/identity/protocols/application-default-credentials
     */
    public function isUsingApplicationDefaultCredentials()
    {
        return $this->config['use_application_default_credentials'];
    }

    /**
     * Set the access token used for requests.
     *
     * Note that at the time requests are sent, tokens are cached. A token will be
     * cached for each combination of service and authentication scopes. If a
     * cache pool is not provided, creating a new instance of the client will
     * allow modification of access tokens. If a persistent cache pool is
     * provided, in order to change the access token, you must clear the cached
     * token by calling `$client->getCache()->clear()`. (Use caution in this case,
     * as calling `clear()` will remove all cache items, including any items not
     * related to Google API PHP Client.)
     *
     * @param string|array $token
     * @throws InvalidArgumentException
     */
    public function setAccessToken($token)
    {
        if (is_string($token)) {
            if ($json = json_decode($token, true)) {
                $token = $json;
            } else {
                // assume $token is just the token string
                $token = array(
                    'access_token' => $token,
                );
            }
        }
        if ($token == null) {
            throw new \Exception('invalid json token');
        }
        if (!isset($token['access_token'])) {
            throw new \Exception("Invalid token format");
        }
        $this->token = $token;
    }

    public function getAccessToken()
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken()
    {
        if (isset($this->token['refresh_token'])) {
            return $this->token['refresh_token'];
        }

        return null;
    }

    /**
     * Returns if the access_token is expired.
     * @return bool Returns True if the access_token is expired.
     */
    public function isAccessTokenExpired()
    {
        if (!$this->token) {
            return true;
        }

        $created = 0;
        if (isset($this->token['created'])) {
            $created = $this->token['created'];
        } elseif (isset($this->token['id_token'])) {
            // check the ID token for "iat"
            // signature verification is not required here, as we are just
            // using this for convenience to save a round trip request
            // to the Google API server
            $idToken = $this->token['id_token'];
            if (substr_count($idToken, '.') == 2) {
                $parts = explode('.', $idToken);
                $payload = json_decode(base64_decode($parts[1]), true);
                if ($payload && isset($payload['iat'])) {
                    $created = $payload['iat'];
                }
            }
        }

        // If the token is set to expire in the next 30 seconds.
        return ($created + ($this->token['expires_in'] - 30)) < time();
    }

    /**
     * @deprecated See UPGRADING.md for more information
     */
    public function getAuth()
    {
        throw new \Exception(
            'This function no longer exists. See UPGRADING.md for more information'
        );
    }

    /**
     * @deprecated See UPGRADING.md for more information
     */
    public function setAuth($auth)
    {
        throw new \Exception(
            'This function no longer exists. See UPGRADING.md for more information'
        );
    }

    /**
     * Set the OAuth 2.0 Client ID.
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->config['client_id'] = $clientId;
    }

    public function getClientId()
    {
        return $this->config['client_id'];
    }

    /**
     * Set the scopes to be requested. Must be called before createAuthUrl().
     * Will remove any previously configured scopes.
     * @param string|array $scope_or_scopes , ie: array('https://www.googleapis.com/auth/plus.login',
     * 'https://www.googleapis.com/auth/moderator')
     */
    public function setScopes($scope_or_scopes)
    {
        $this->requestedScopes = array();
        $this->addScope($scope_or_scopes);
    }

    /**
     * This functions adds a scope to be requested as part of the OAuth2.0 flow.
     * Will append any scopes not previously requested to the scope parameter.
     * A single string will be treated as a scope to request. An array of strings
     * will each be appended.
     * @param $scope_or_scopes string|array e.g. "profile"
     */
    public function addScope($scope_or_scopes)
    {
        if (is_string($scope_or_scopes) && !in_array($scope_or_scopes, $this->requestedScopes)) {
            $this->requestedScopes[] = $scope_or_scopes;
        } else if (is_array($scope_or_scopes)) {
            foreach ($scope_or_scopes as $scope) {
                $this->addScope($scope);
            }
        }
    }

    /**
     * Returns the list of scopes requested by the client
     * @return array the list of scopes
     *
     */
    public function getScopes()
    {
        return $this->requestedScopes;
    }

    /**
     * @return string|null
     * @visible For Testing
     */
    public function prepareScopes()
    {
        if (empty($this->requestedScopes)) {
            return null;
        }

        return implode(' ', $this->requestedScopes);
    }

    /**
     * Are we running in Google AppEngine?
     * return bool
     */
    public function isAppEngine()
    {
        return (isset($_SERVER['SERVER_SOFTWARE']) &&
            strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') !== false);
    }

    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function getConfig($name, $default = null)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

    /**
     * For backwards compatibility
     * alias for setAuthConfig
     *
     * @param string $file the configuration file
     * @throws Google_Exception
     * @deprecated
     */
    public function setAuthConfigFile($file)
    {
        $this->setAuthConfig($file);
    }

    /**
     * Set the auth config from new or deprecated JSON config.
     * This structure should match the file downloaded from
     * the "Download JSON" button on in the Google Developer
     * Console.
     * @param string|array $config the configuration json
     * @throws Google_Exception
     */
    public function setAuthConfig($config)
    {
        if (is_string($config)) {
            if (!file_exists($config)) {
                throw new \Exception(sprintf('file "%s" does not exist', $config));
            }

            $json = file_get_contents($config);

            if (!$config = json_decode($json, true)) {
                throw new \Exception('invalid json for auth config');
            }
        }

        $key = isset($config['installed']) ? 'installed' : 'web';
        if (isset($config['type']) && $config['type'] == 'service_account') {
            // application default credentials
            $this->useApplicationDefaultCredentials();

            // set the information from the config
            $this->setClientId($config['client_id']);
            $this->config['client_email'] = $config['client_email'];
            $this->config['signing_key'] = $config['private_key'];
            $this->config['signing_algorithm'] = 'HS256';
        } elseif (isset($config[$key])) {
            // old-style
            $this->setClientId($config[$key]['client_id']);
            $this->setClientSecret($config[$key]['client_secret']);
            if (isset($config[$key]['redirect_uris'])) {
                $this->setRedirectUri($config[$key]['redirect_uris'][0]);
            }
        } else {
            // new-style
            $this->setClientId($config['client_id']);
            $this->setClientSecret($config['client_secret']);
            if (isset($config['redirect_uris'])) {
                $this->setRedirectUri($config['redirect_uris'][0]);
            }
        }
    }


    /**
     * @return Psr\Log\LoggerInterface implementation
     */
    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = $this->createDefaultLogger();
        }

        return $this->logger;
    }

    protected function createDefaultLogger()
    {
        $logger = new Logger('google-api-php-client');
        if ($this->isAppEngine()) {
            $handler = new MonologSyslogHandler('app', LOG_USER, Logger::NOTICE);
        } else {
            $handler = new MonologStreamHandler('php://stderr', Logger::NOTICE);
        }
        $logger->pushHandler($handler);

        return $logger;
    }


    /**
     * Set the Http Client object
     * @param GuzzleHttp\ClientInterface $http
     */
    public function setHttpClient(ClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * @return GuzzleHttp\ClientInterface implementation
     */
    public function getHttpClient()
    {
        if (null === $this->http) {
            $this->http = $this->createDefaultHttpClient();
        }

        return $this->http;
    }


    protected function createDefaultHttpClient()
    {
        $options = ['exceptions' => false];

        $version = ClientInterface::VERSION;
        if ('5' === $version[0]) {
            $options = [
                'base_url' => $this->config['base_path'],
                'defaults' => $options,
            ];
            if ($this->isAppEngine()) {
                // set StreamHandler on AppEngine by default
                $options['handler'] = new StreamHandler();
                $options['defaults']['verify'] = '/etc/ca-certificates.crt';
            }
        } else {
            // guzzle 6
            $options['base_uri'] = $this->config['base_path'];
        }

        return new Client($options);
    }

    private function createApplicationDefaultCredentials()
    {
        $scopes = $this->prepareScopes();
        $sub = $this->config['subject'];
        $signingKey = $this->config['signing_key'];

        // create credentials using values supplied in setAuthConfig
        if ($signingKey) {
            $serviceAccountCredentials = array(
                'client_id' => $this->config['client_id'],
                'client_email' => $this->config['client_email'],
                'private_key' => $signingKey,
                'type' => 'service_account',
                'quota_project' => $this->config['quota_project'],
            );
            $credentials = CredentialsLoader::makeCredentials(
                $scopes,
                $serviceAccountCredentials
            );
        } else {
            $credentials = ApplicationDefaultCredentials::getCredentials(
                $scopes,
                null,
                null,
                null,
                $this->config['quota_project']
            );
        }

        // for service account domain-wide authority (impersonating a user)
        // @see https://developers.google.com/identity/protocols/OAuth2ServiceAccount
        if ($sub) {
            if (!$credentials instanceof ServiceAccountCredentials) {
                throw new \Exception('domain-wide authority requires service account credentials');
            }

            $credentials->setSub($sub);
        }
        return $credentials;
    }

}
