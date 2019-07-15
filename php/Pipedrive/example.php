<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/PipedriveManager.php';

$pipedrive = new App\PipedriveManager('YourPipedriveTokenShouldGoHere');

/*
 * These methods are currently available to use.
 *
 * * * * DEALS * * * *
 * $pipedrive->getDeals()
 * $pipedrive->getDealsSummary($status = '', $filterId = 0, $userId = 0, $stageId = 0)
 * $pipedrive->getDealByName($name)
 * $pipedrive->getDealById($dealId)
 * $pipedrive->getDealActivities($dealId, $start = 0, $limit = 0, $done = -1)
 * $pipedrive->getDealFiles($dealId, $start = 0, $limit = 0)
 * $pipedrive->getDealUpdates($dealId, $start = 0, $limit = 0)
 * $pipedrive->getDealFollowers($dealId)
 * $pipedrive->getDealMessages($dealId, $start = 0, $limit = 0)
 * $pipedrive->getDealParticipants($dealId, $start = 0, $limit = 0)
 * $pipedrive->getDealPermittedUsers($dealId, $accessLevel = 0)
 * $pipedrive->getDealPersons($dealId, $start = 0, $limit = 0)
 * $pipedrive->getDealProducts($dealId, $start = 0, $limit = 0)
 *
 * * * * DEAL FIELDS * * * *
 * $pipedrive->getDealFields()
 *
 * * * * FILES * * * *
 * $pipedrive->getFiles($start = 0, $limit = 0)
 * $pipedrive->getFileById($fileId)
 *
 * * * * GLOBAL MESSAGES * * * *
 * $pipedrive->getGlobalMessages()
 *
 * * * * MAIL MESSAGES * * * *
 * $pipedrive->getMailMessageById($messageId, $includeBody = 0)
 *
 * * * * MAIL THREADS * * * *
 * $pipedrive->getMailThreads($folder = 'inbox', $start = 0, $limit = 0)
 * $pipedrive->getMailThreadById($threadId)
 * $pipedrive->getMailThreadMessages($threadId)
 *
 * * * * NOTES * * * *
 * $pipedrive->getNotes($start = 0, $limit = 0)
 * $pipedrive->getNoteById($noteId)
 *
 * * * * NOTE FIELDS * * * *
 * $pipedrive->getNoteFields()
 *
 * * * * ORGANIZATIONS * * * *
 * $pipedrive->getOrganizations($start = 0, $limit = 0)
 * $pipedrive->getOrganizationByName($name, $start = 0, $limit = 0)
 * $pipedrive->getOrganizationDetails($id)
 * $pipedrive->getOrganizationActivities($id, $start = 0, $limit = 0, $done = -1)
 * $pipedrive->getOrganizationDeals($id, $start = 0, $limit = 0, $status = '')
 * $pipedrive->getOrganizationFiles($id, $start = 0, $limit = 0)
 * $pipedrive->getOrganizationUpdates($id, $start = 0, $limit = 0)
 * $pipedrive->getOrganizationFollowers($id)
 * $pipedrive->getOrganizationMessages($id, $start = 0, $limit = 0)
 * $pipedrive->getOrganizationPermittedUsers($id, $accessLevel = 0)
 * $pipedrive->getOrganizationPersons($id, $start = 0, $limit = 0)
 *
 * * * * ORGANIZATION FIELDS * * * *
 * $pipedrive->getOrganizationFields()
 *
 * * * * PERSONS * * * *
 * $pipedrive->getPersons($start = 0, $limit = 0)
 * $pipedrive->getPersonByName($name, $start = 0, $limit = 0)
 * $pipedrive->getPersonDetails($personId)
 * $pipedrive->getPersonActivities($personId, $start = 0, $limit = 0, $done = -1)
 * $pipedrive->getPersonDeals($personId, $start = 0, $limit = 0, $status = '')
 * $pipedrive->getPersonFiles($personId, $start = 0, $limit = 0)
 * $pipedrive->getPersonUpdates($personId, $start = 0, $limit = 0)
 * $pipedrive->getPersonFollowers($personId)
 * $pipedrive->getPersonMessages($personId, $start = 0, $limit = 0)
 * $pipedrive->getPersonPermittedUsers($personId, $accessLevel = 0)
 * $pipedrive->getPersonProducts($personId, $start = 0, $limit = 0)
 *
 * * * * PERSON FIELDS * * * *
 * $pipedrive->getPersonFields($start = 0, $limit = 0)
 * $pipedrive->getPersonField($fieldId)
 *
 * * * * PIPELINES * * * *
 * $pipedrive->getPipelines()
 * $pipedrive->getPipeline($pipelineId)
 * $pipedrive->getPipelineConversionRate($pipelineId, $startDate, $endDate)
 * $pipedrive->getPipelineDeals($pipelineId, $start = 0, $limit = 0, $filterId = 0, $userId = 0, $stageId = 0
 * $pipedrive->getPipelineMovements($pipelineId, $startDate, $endDate)
 *
 * * * * PRODUCTS * * * *
 * $pipedrive->getProducts($start = 0, $limit = 0, $userId = 0, $filterId = 0)
 * $pipedrive->getProductsByName($name, $start = 0, $limit = 0)
 * $pipedrive->getProductById($productId)
 * $pipedrive->getProductDeals($productId, $start = 0, $limit = 0, $status = '')
 * $pipedrive->getProductFiles($productId, $start = 0, $limit = 0)
 * $pipedrive->getProductFollowers($productId)
 * $pipedrive->getProductPermittedUsers($productId, $accessLevel = 0)
 *
 * * * * PRODUCT FIELDS * * * *
 * $pipedrive->getProductFields()
 * $pipedrive->getProductField($fieldId)
 *
 * * * * ROLES * * * *
 * $pipedrive->getRoles($start = 0, $limit = 0)
 * $pipedrive->getRoleById($roleId)
 * $pipedrive->getRoleAssignments($roleId, $start = 0, $limit = 0)
 * $pipedrive->getRoleSubRoles($roleId, $start = 0, $limit = 0)
 * $pipedrive->getRoleSettings($roleId)
 *
 * * * * STAGES * * * *
 * $pipedrive->getStages($pipelineId = 0)
 * $pipedrive->getStageById($stageId)
 * $pipedrive->getStageDeals($stageId, $start = 0, $limit = 0, $filterId = 0, $userId = 0)
 *
 * * * * USERS * * * *
 * $pipedrive->getUsers()
 * $pipedrive->getUsersByName($name)
 * $pipedrive->getUsersByEmail($email)
 * $pipedrive->getUserData()
 * $pipedrive->getUserById($userId)
 * $pipedrive->getUserBlacklistedEmails($userId)
 * $pipedrive->getUserFollowers($userId)
 * $pipedrive->getUserPermissions($userId)
 * $pipedrive->getUserRoleAssignments($userId, $start = 0, $limit = 0)
 * $pipedrive->getUserRoleSettings($userId)
 * $pipedrive->getUserConnections()
 * $pipedrive->getUserSettings()
 *
 * * * * WEBHOOKS * * * *
 * $pipedrive->getWebhooks()
 *
 */

var_dump($pipedrive->getDeals());