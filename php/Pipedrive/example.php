<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/PipedriveManager.php';

$pipedrive = new App\PipedriveManager('YourPipedriveTokenShouldGoHere');

/*
 * These methods are currently available to use.
 *
 * * * * DEALS * * * *
 * $pipedrive->getDeals()
 * $pipedrive->getDealsSummary(string $status = '', int $filterId = 0, int $userId = 0, int $stageId = 0)
 * $pipedrive->getDealByName(string $name)
 * $pipedrive->getDealById(int $dealId)
 * $pipedrive->getDealActivities(int $dealId, int $start = 0, int $limit = 0, int $done = -1)
 * $pipedrive->getDealFiles(int $dealId, int $start = 0, int $limit = 0)
 * $pipedrive->getDealUpdates(int $dealId, int $start = 0, int $limit = 0)
 * $pipedrive->getDealFollowers(int $dealId)
 * $pipedrive->getDealMessages(int $dealId, int $start = 0, int $limit = 0)
 * $pipedrive->getDealParticipants(int $dealId, int $start = 0, int $limit = 0)
 * $pipedrive->getDealPermittedUsers(int $dealId, int $accessLevel = 0)
 * $pipedrive->getDealPersons(int $dealId, int $start = 0, int $limit = 0)
 * $pipedrive->getDealProducts(int $dealId, int $start = 0, int $limit = 0)
 *
 * * * * DEAL FIELDS * * * *
 * $pipedrive->getDealFields()
 *
 * * * * FILES * * * *
 * $pipedrive->getFiles(int $start = 0, int $limit = 0)
 * $pipedrive->getFileById(int $fileId)
 *
 * * * * GLOBAL MESSAGES * * * *
 * $pipedrive->getGlobalMessages()
 *
 * * * * MAIL MESSAGES * * * *
 * $pipedrive->getMailMessageById(int $messageId, int $includeBody = 0)
 *
 * * * * MAIL THREADS * * * *
 * $pipedrive->getMailThreads(string $folder = 'inbox', int $start = 0, int $limit = 0)
 * $pipedrive->getMailThreadById(int $threadId)
 * $pipedrive->getMailThreadMessages(int $threadId)
 *
 * * * * NOTES * * * *
 * $pipedrive->getNotes(int $start = 0, int $limit = 0)
 * $pipedrive->getNoteById(int $noteId)
 *
 * * * * NOTE FIELDS * * * *
 * $pipedrive->getNoteFields()
 *
 * * * * ORGANIZATIONS * * * *
 * $pipedrive->getOrganizations(int $start = 0, int $limit = 0)
 * $pipedrive->getOrganizationByName(string $name, int $start = 0, int $limit = 0)
 * $pipedrive->getOrganizationDetails(int $id)
 * $pipedrive->getOrganizationActivities(int $id, int $start = 0, int $limit = 0, int $done = -1)
 * $pipedrive->getOrganizationDeals(int $id, int $start = 0, int $limit = 0, string $status = '')
 * $pipedrive->getOrganizationFiles(int $id, int $start = 0, int $limit = 0)
 * $pipedrive->getOrganizationUpdates(int $id, int $start = 0, int $limit = 0)
 * $pipedrive->getOrganizationFollowers(int $id)
 * $pipedrive->getOrganizationMessages(int $id, int $start = 0, int $limit = 0)
 * $pipedrive->getOrganizationPermittedUsers(int $id, int $accessLevel = 0)
 * $pipedrive->getOrganizationPersons(int $id, int $start = 0, int $limit = 0)
 *
 * * * * ORGANIZATION FIELDS * * * *
 * $pipedrive->getOrganizationFields()
 *
 * * * * PERSONS * * * *
 * $pipedrive->getPersons(int $start = 0, int $limit = 0)
 * $pipedrive->getPersonByName(string $name, int $start = 0, int $limit = 0)
 * $pipedrive->getPersonDetails(int $personId)
 * $pipedrive->getPersonActivities(int $personId, int $start = 0, int $limit = 0, int $done = -1)
 * $pipedrive->getPersonDeals(int $personId, int $start = 0, int $limit = 0, string $status = '')
 * $pipedrive->getPersonFiles(int $personId, int $start = 0, int $limit = 0)
 * $pipedrive->getPersonUpdates(int $personId, int $start = 0, int $limit = 0)
 * $pipedrive->getPersonFollowers(int $personId)
 * $pipedrive->getPersonMessages(int $personId, int $start = 0, int $limit = 0)
 * $pipedrive->getPersonPermittedUsers(int $personId, int $accessLevel = 0)
 * $pipedrive->getPersonProducts(int $personId, int $start = 0, int $limit = 0)
 *
 * * * * PERSON FIELDS * * * *
 * $pipedrive->getPersonFields(int $start = 0, int $limit = 0)
 * $pipedrive->getPersonField(int $fieldId)
 *
 * * * * PIPELINES * * * *
 * $pipedrive->getPipelines()
 * $pipedrive->getPipeline(int $pipelineId)
 * $pipedrive->getPipelineConversionRate(int $pipelineId, DateTime $startDate, DateTime $endDate)
 * $pipedrive->getPipelineDeals(int $pipelineId, int $start = 0, int $limit = 0, int $filterId = 0, int $userId = 0, int $stageId = 0
 * $pipedrive->getPipelineMovements(int $pipelineId, DateTime $startDate, DateTime $endDate)
 *
 * * * * PRODUCTS * * * *
 * $pipedrive->getProducts(int $start = 0, int $limit = 0, int $userId = 0, int $filterId = 0)
 * $pipedrive->getProductsByName(string $name, int $start = 0, int $limit = 0)
 * $pipedrive->getProductById(int $productId)
 * $pipedrive->getProductDeals(int $productId, int $start = 0, int $limit = 0, string $status = '')
 * $pipedrive->getProductFiles(int $productId, int $start = 0, int $limit = 0)
 * $pipedrive->getProductFollowers(int $productId)
 * $pipedrive->getProductPermittedUsers(int $productId, int $accessLevel = 0)
 *
 * * * * PRODUCT FIELDS * * * *
 * $pipedrive->getProductFields()
 * $pipedrive->getProductField(int $fieldId)
 *
 * * * * ROLES * * * *
 * $pipedrive->getRoles(int $start = 0, int $limit = 0)
 * $pipedrive->getRoleById(int $roleId)
 * $pipedrive->getRoleAssignments(int $roleId, int $start = 0, int $limit = 0)
 * $pipedrive->getRoleSubRoles(int $roleId, int $start = 0, int $limit = 0)
 * $pipedrive->getRoleSettings(int $roleId)
 *
 * * * * STAGES * * * *
 * $pipedrive->getStages(int $pipelineId = 0)
 * $pipedrive->getStageById(int $stageId)
 * $pipedrive->getStageDeals(int $stageId, int $start = 0, int $limit = 0, int $filterId = 0, int $userId = 0)
 *
 * * * * USERS * * * *
 * $pipedrive->getUsers()
 * $pipedrive->getUsersByName(string $name)
 * $pipedrive->getUsersByEmail(string $email)
 * $pipedrive->getUserData()
 * $pipedrive->getUserById(int $userId)
 * $pipedrive->getUserBlacklistedEmails(int $userId)
 * $pipedrive->getUserFollowers(int $userId)
 * $pipedrive->getUserPermissions(int $userId)
 * $pipedrive->getUserRoleAssignments(int $userId, int $start = 0, int $limit = 0)
 * $pipedrive->getUserRoleSettings(int $userId)
 * $pipedrive->getUserConnections()
 * $pipedrive->getUserSettings()
 *
 * * * * WEBHOOKS * * * *
 * $pipedrive->getWebhooks()
 *
 */

var_dump($pipedrive->getDeals());
