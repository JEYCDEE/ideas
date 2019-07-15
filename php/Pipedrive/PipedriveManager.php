<?php
/**
 * This base class aims to help you with PipedriveManager communication.
 * https://www.pipedrive.com
 *
 * @author  : Volodymyr Mon
 * @license : MIT
 * @version : Beta (1.0)
 */

namespace App;

require_once __DIR__ . '/PipedriveHelper.php';

use App\PipedriveHelper;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class PipedriveManager
 *
 * @package App
 */
class PipedriveManager
{

    /**
     * @var string
     */
    public $pipedriveUrl;

    /**
     * @var bool
     */
    public $hasErrors = false;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var string
     */
    private $token;

    /**
     * PipedriveManager constructor.
     *
     * @param string $token
     * @param string $pipedriveUrl
     */
    public function __construct(string $token, string $pipedriveUrl = "https://api.pipedrive.com")
    {

        $this->token        = $token;
        $this->pipedriveUrl = $pipedriveUrl;

    }

    /**
     * @param string $method
     * @param string $route
     * @param callable $handler
     *
     * @return mixed
     *
     * @throws Exception | GuzzleException
     */
    public function handleRequest(string $method, string $route, callable $handler)
    {

        /** @var Client $client */
        /** @var string $response */
        /** @var Exception $e */

        try {

            PipedriveHelper::normalizeUrl($route);

            $uri      = PipedriveHelper::appendUrlWithToken($route, $this->token);
            $client   = new Client(['base_uri' => $this->pipedriveUrl]);
            $response = $client->request($method, $uri)->getBody()->getContents();

            return $handler($response);

        } catch (Exception $e) {

            $this->hasErrors = true;
            $this->errors[]  = 'PipedriveManager.handleRequest : ' . $e->getMessage();

            return [
                'success' => false,
                'errors'  => $this->errors
            ];

        }

    }

    /**
     * Find all supported currencies.
     *
     * @param string $currency
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getCurrencies(string $currency = ''): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = '/v1/currencies';

        if (!empty($currency)) {

            $route .= PipedriveHelper::defineConnector($route) . "term={$currency}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all deals.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDeals(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = '/v1/deals';

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deals summary.
     *
     * @param string $status : could be 'open', 'won' or 'lost'
     * @param int $filterId  : get only matching the given filter. $userId will not be considered. 0 = ignore the rule.
     * @param int $userId    : get only matching the given user. Check $filterId rule. 0 = ignore the rule.
     * @param int $stageId   : only deals within the given stage will be returned. 0 = ignore the rule.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealsSummary(string $status = '', int $filterId = 0, int $userId = 0, int $stageId = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route  = '/v1/deals/summary';

        if (!empty($status) && in_array($status, ['open', 'won', 'lost'])) {

            $route .= PipedriveHelper::defineConnector($route) . "status={$status}";

        }

        if (!empty($filterId)) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$filterId}";

        }

        if (!empty($userId)) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$userId}";

        }

        if (!empty($stageId)) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$stageId}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deal by name.
     *
     * @param string $name
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealByName(string $name): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/find?term={$name}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deal by id.
     *
     * @param int $dealId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealById(int $dealId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all activities associated with a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     * @param int $done  : -1 = ignore the rule, 0 = undone activities, 1 = done activities.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealActivities(int $dealId, int $start = 0, int $limit = 0, int $done = -1): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/activities?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($done, [0, 1])) {

            $route .= PipedriveHelper::defineConnector($route) . "done={$done}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all files associated with a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealFiles(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/activities?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all updates associated with a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealUpdates(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/updates?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all followers of a deal.
     *
     * @param int $dealId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealFollowers(int $dealId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/followers";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all mail messages associated with a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealMessages(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/mailMessages?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all participants of a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealParticipants(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/participants?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all permitted users of a deal.
     *
     * @param int $dealId
     * @param int $accessLevel : filter by access level. 0 = ignore the rule, 1 = read, 2 = write, 3 = read+write
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealPermittedUsers(int $dealId, int $accessLevel = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/permittedUsers";

        if (in_array($accessLevel, [1, 2, 3])) {

            $route .= PipedriveHelper::defineConnector($route) . "access_level={$accessLevel}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all participants of a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealPersons(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/persons?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all products attached to a deal.
     *
     * @param int $dealId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealProducts(int $dealId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/deals/{$dealId}/products?start=$start";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all deal fields.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getDealFields(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/dealFields?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all file.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getFiles(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/files?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one file.
     *
     * @param int $fileId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getFileById(int $fileId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/files/{$fileId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find global messages.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getGlobalMessages(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/globalMessages";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one mail message.
     *
     * @param int $messageId
     * @param int $includeBody : whether to include full message body or not. 0 = don't include, 1 = include
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMailMessageById(int $messageId, int $includeBody = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/mailbox/mailMessages/{$messageId}";

        if (in_array($includeBody, [0, 1])) {

            $route .= PipedriveHelper::defineConnector($route) . "include_body={$includeBody}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find mail threads.
     *
     * @param string $folder : could be 'inbox', 'drafts', 'sent' and 'archive'.
     * @param int $start     : pagination start.
     * @param int $limit     : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMailThreads(string $folder = 'inbox', int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/mailbox/mailThreads?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($folder, ['inbox', 'drafts', 'sent', 'archive'])) {

            $route .= PipedriveHelper::defineConnector($route) . "folder={$folder}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one mail thread.
     *
     * @param int $threadId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMailThreadById(int $threadId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/mailbox/mailThreads/{$threadId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all mail messages of mail thread.
     *
     * @param int $threadId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMailThreadMessages(int $threadId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/mailbox/mailThreads/{$threadId}/mailMessages";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all notes.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getNotes(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/notes?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one note.
     *
     * @param int $noteId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getNoteById(int $noteId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/notes/{$noteId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find note fields.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getNoteFields(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/noteFields";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all organizations.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizations(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find organization by name.
     *
     * @param string $name
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationByName(string $name, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/find?term={$name}";

        if ($start >= 0) {

            $route .= PipedriveHelper::defineConnector($route) . "start={$start}";

        }

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find organization details by id.
     *
     * @param int $id
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationDetails(int $id): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all activities associated with an organization.
     *
     * @param int $id
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     * @param int $done  : -1 = ignore the rule, 0 = undone activities, 1 = done activities.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationActivities(int $id, int $start = 0, int $limit = 0, int $done = -1): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/activities?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($done, [0, 1])) {

            $route .= PipedriveHelper::defineConnector($route) . "done={$done}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all deals associated with an organization.
     *
     * @param int $id
     * @param int $start     : pagination start.
     * @param int $limit     : items shown per page, 0 = no limit.
     * @param string $status : could be 'open', 'won', 'lost' and 'deleted'.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationDeals(int $id, int $start = 0, int $limit = 0, string $status = ''): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/deals?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($status, ['open', 'won', 'lost', 'deleted'])) {

            $route .= PipedriveHelper::defineConnector($route) . "status={$status}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all files attached to an organization.
     *
     * @param int $id
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationFiles(int $id, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/files?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all updates associated with an organization.
     *
     * @param int $id
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationUpdates(int $id, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/flow?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all followers associated with an organization.
     *
     * @param int $id
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationFollowers(int $id): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/followers";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all mail messages associated with an organization.
     *
     * @param int $id
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationMessages(int $id, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/mailMessages?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all permitted users associated with an organization.
     *
     * @param int $id
     * @param int $accessLevel : filter by access level. 0 = ignore the rule, 1 = read, 2 = write, 3 = read+write
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationPermittedUsers(int $id, int $accessLevel = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/permittedUsers";

        if (in_array($accessLevel, [1, 2, 3])) {

            $route .= PipedriveHelper::defineConnector($route) . "access_level={$accessLevel}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all persons of an organization.
     *
     * @param int $id
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getOrganizationPersons(int $id, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizations/{$id}/persons?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all organization fields.
     *
     * @return array
     *
     *
     * @throws GuzzleException
     */
    public function getOrganizationFields(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/organizationFields";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all persons.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersons(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find person by name.
     *
     * @param string $name
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonByName(string $name, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/find?term={$name}&start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find details of a person.
     *
     * @param int $personId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonDetails(int $personId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find activities associated with a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     * @param int $done  : -1 = ignore the rule, 0 = undone activities, 1 = done activities.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonActivities(int $personId, int $start = 0, int $limit = 0, int $done = -1): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/activities?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($done, [0, 1])) {

            $route .= PipedriveHelper::defineConnector($route) . "done={$done}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deals associated with a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     * @param string $status : could be 'open', 'won', 'lost' and 'deleted'.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonDeals(int $personId, int $start = 0, int $limit = 0, string $status = ''): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/deals?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($status, ['open', 'won', 'lost', 'deleted'])) {

            $route .= PipedriveHelper::defineConnector($route) . "status={$status}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find files associated with a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonFiles(int $personId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/files?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find updates about a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonUpdates(int $personId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/flow?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find followers of a person.
     *
     * @param int $personId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonFollowers(int $personId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/followers";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find mail messages associated with a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonMessages(int $personId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/mailMessages?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find permitted users, who can access this person.
     *
     * @param int $personId
     * @param int $accessLevel : filter by access level. 0 = ignore the rule, 1 = read, 2 = write, 3 = read+write
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonPermittedUsers(int $personId, int $accessLevel = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/permittedUsers";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($accessLevel, [1, 2, 3])) {

            $route .= PipedriveHelper::defineConnector($route) . "access_level={$accessLevel}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find products associated with a person.
     *
     * @param int $personId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonProducts(int $personId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/persons/{$personId}/products?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all person fields.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonFields(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/personFields?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one person field.
     *
     * @param int $fieldId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPersonField(int $fieldId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/personFields/{$fieldId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all pipelies.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPipelines(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/pipelines";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one pipeline.
     *
     * @param int $pipelineId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPipeline(int $pipelineId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/pipelines/{$pipelineId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deal conversion rates in a pipeline.
     *
     * @param int $pipelineId
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPipelineConversionRate(int $pipelineId, DateTime $startDate, DateTime $endDate): array
    {

        /** @var array $result */
        /** @var string $route */

        $route  = "/v1/pipelines/{$pipelineId}/conversion_statistics";
        $route .= "?start_date={$startDate->format('Y-m-d')}";
        $route .= "&end_date={$endDate->format('Y-m-d')}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deals in a pipeline.
     *
     * @param int $pipelineId
     * @param int $start    : pagination start.
     * @param int $limit    : items shown per page, 0 = no limit.
     * @param int $filterId : find only deals matching the given filter.
     * @param int $userId   : find only deals owned by the given user. If supplied, $filterId will not be considered.
     * @param int $stageId  : find only deals within the given stage.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPipelineDeals(int $pipelineId, int $start = 0, int $limit = 0, int $filterId = 0,
                                     int $userId = 0, int $stageId = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route  = "/v1/pipelines/{$pipelineId}/deals?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if ($filterId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$filterId}";

        }

        if ($userId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "user_id={$userId}";

        }

        if ($stageId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "stage_id={$stageId}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deal movements in a pipeline.
     *
     * @param int $pipelineId
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getPipelineMovements(int $pipelineId, DateTime $startDate, DateTime $endDate): array
    {

        /** @var array $result */
        /** @var string $route */

        $route  = "/v1/pipelines/{$pipelineId}/movement_statistics";
        $route .= "?start_date={$startDate->format('Y-m-d')}";
        $route .= "&end_date={$endDate->format('Y-m-d')}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all products.
     *
     * @param int $start    : pagination start.
     * @param int $limit    : items shown per page, 0 = no limit.
     * @param int $userId   : find only products owned by the given user.
     * @param int $filterId : id of the filter to use.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProducts(int $start = 0, int $limit = 0, int $userId = 0, int $filterId = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if ($userId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "user_id={$userId}";

        }

        if ($filterId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$filterId}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one product by name.
     *
     * @param string $name
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductsByName(string $name, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/find?term={$name}&start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }
        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one product by id.
     *
     * @param int $productId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductById(int $productId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/{$productId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deals where a product is attached to.
     *
     * @param int $productId
     * @param int $start     : pagination start.
     * @param int $limit     : items shown per page, 0 = no limit.
     * @param string $status : could be 'open', 'won', 'lost' and 'deleted'.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductDeals(int $productId, int $start = 0, int $limit = 0, string $status = ''): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/{$productId}/deals?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if (in_array($status, ['open', 'won', 'lost', 'deleted'])) {

            $route .= PipedriveHelper::defineConnector($route) . "status={$status}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find files attached to a product.
     *
     * @param int $productId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductFiles(int $productId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/{$productId}/files?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all followers of a product.
     *
     * @param int $productId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductFollowers(int $productId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/{$productId}/followers";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all permitted users of a product.
     *
     * @param int $productId
     * @param int $accessLevel : filter by access level. 0 = ignore the rule, 1 = read, 2 = write, 3 = read+write
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductPermittedUsers(int $productId, int $accessLevel = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/products/{$productId}/permittedUsers";

        if (in_array($accessLevel, [1, 2, 3])) {

            $route .= PipedriveHelper::defineConnector($route) . "access_level={$accessLevel}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all product fields.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductFields(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/productFields";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one product field.
     *
     * @param int $fieldId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getProductField(int $fieldId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/productFields/{$fieldId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all roles.
     *
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getRoles(int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/roles?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one role.
     *
     * @param int $roleId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getRoleById(int $roleId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/roles/{$roleId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find role assignments.
     *
     * @param int $roleId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getRoleAssignments(int $roleId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/roles/{$roleId}/assignments?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find role sub-roles.
     *
     * @param int $roleId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getRoleSubRoles(int $roleId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/roles/{$roleId}/roles?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find role settings.
     *
     * @param int $roleId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getRoleSettings(int $roleId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/roles/{$roleId}/settings";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all stages.
     *
     * @param int $pipelineId : id of the pipeline to fetch stages for. If omitted, all pipelines stages will be fetched
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getStages(int $pipelineId = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/stages";

        if ($pipelineId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "pipeline_id={$pipelineId}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all stages.
     *
     * @param int $stageId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getStageById(int $stageId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/stages/{$stageId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find deals in a stage.
     *
     * @param int $stageId  : find only deals within the given stage.
     * @param int $start    : pagination start.
     * @param int $limit    : items shown per page, 0 = no limit.
     * @param int $filterId : find only deals matching the given filter.
     * @param int $userId   : find only deals owned by the given user. If supplied, $filterId will not be considered.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getStageDeals(int $stageId, int $start = 0, int $limit = 0, int $filterId = 0,
                                  int $userId = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route  = "/v1/stages/{$stageId}/deals?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        if ($filterId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "filter_id={$filterId}";

        }

        if ($userId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "user_id={$userId}";

        }

        if ($stageId > 0) {

            $route .= PipedriveHelper::defineConnector($route) . "stage_id={$stageId}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all users.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUsers(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all users by name.
     *
     * @param string $name
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUsersByName(string $name): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/find?term={$name}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all users by email address.
     *
     * @param string $email
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUsersByEmail(string $email): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/find?term={$email}&search_by_email=true";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Get current user data.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserData(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/me";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find one user by id.
     *
     * @param int $userId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserById(int $userId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find blacklisted email addresses of a user.
     *
     * @param int $userId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserBlacklistedEmails(int $userId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}/blacklistedEmails";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find followers of a user.
     *
     * @param int $userId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserFollowers(int $userId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}/followers";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find user permissions.
     *
     * @param int $userId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserPermissions(int $userId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}/permissions";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find user role assignments.
     *
     * @param int $userId
     * @param int $start : pagination start.
     * @param int $limit : items shown per page, 0 = no limit.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserRoleAssignments(int $userId, int $start = 0, int $limit = 0): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}/roleAssignments?start={$start}";

        if (!empty($limit)) {

            $route .= PipedriveHelper::defineConnector($route) . "limit={$limit}";

        }

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find user role settings.
     *
     * @param int $userId
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserRoleSettings(int $userId): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/users/{$userId}/roleSettings";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find current user connections.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserConnections(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/usersConnections";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find current user settings.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserSettings(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/usersSettings";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

    /**
     * Find all stored webhooks.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function getWebhooks(): array
    {

        /** @var array $result */
        /** @var string $route */

        $route = "/v1/webhooks";

        return $this->handleRequest('GET', $route, function($rowContent): array {

            return json_decode($rowContent, true);

        });

    }

}