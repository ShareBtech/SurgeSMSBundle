<?php

declare(strict_types=1);

namespace MauticPlugin\SurgeBundle\Integration\Messagewhiz;


use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\CoreBundle\Exception\BadConfigurationException;


class Configuration
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var string
     */
    private $senderId;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * Configuration constructor.
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @return string
     *
     * @throws ConfigurationException
     */
    public function getSenderId()
    {
        $this->setConfiguration();

        return $this->senderId;
    }

    /**
     * @return string
     *
     * @throws ConfigurationException
     */
    public function getCampaignId()
    {
        $this->setConfiguration();

        return $this->campaignId;
    }

    /**
     * @return string
     *
     * @throws ConfigurationException
     */
    public function getApiKey()
    {
        $this->setConfiguration();

        return $this->apiKey;
    }

    /**
     * @throws ConfigurationException
     */
    private function setConfiguration()
    {
        if ($this->apiKey) {
            return;
        }

        $integration = $this->integrationHelper->getIntegrationObject('Messagewhiz');

        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            throw new BadConfigurationException();
        }

        $this->senderId = $integration->getIntegrationSettings()->getFeatureSettings()['sender_id'];
        if (empty($this->senderId)) {
            throw new BadConfigurationException();
        }

        $this->campaignId = $integration->getIntegrationSettings()->getFeatureSettings()['default_campaign_id'];
        if (empty($this->campaignId)) {
            throw new BadConfigurationException();
        }

        $keys = $integration->getDecryptedApiKeys();
        if (empty($keys['apiKey'])) {
            throw new BadConfigurationException();
        }

        $this->apiKey = $keys['apiKey'];
    }
}
