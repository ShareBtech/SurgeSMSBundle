<?php


namespace MauticPlugin\SurgeBundle\Integration\Messagewhiz;

use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Sms\TransportInterface;
use Psr\Log\LoggerInterface;
use MauticPlugin\SurgeBundle\Integration\Messagewhiz\Messagewhiz;

class MessagewhizTransport implements TransportInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var LoggerInterface
     */
    private $logger;


    private $senderId;

    private $client;


    /**
     * TwilioTransport constructor.
     */
    public function __construct(Configuration $configuration, LoggerInterface $logger)
    {
        $this->logger        = $logger;
        $this->configuration = $configuration;
    }

    /**
     * @param string $content
     *
     * @return bool|string
     */
    public function sendSms(Lead $lead, $content)
    {
        $number = $lead->getLeadPhoneNumber();

        if (null === $number) {
            return false;
        }
        $this->configureClient();

        $sanitizedNumber = $this->sanitizeNumber($number);

        try {
            $this->configureClient();

            $sanitizedNumber = $this->sanitizeNumber($number);
            $response = $this->client->sendSms(
                [$sanitizedNumber],
                $content,
                $this->senderId,
                null,
                true
            );

            return true;
        } catch (NumberParseException $exception) {
            $this->logger->warning(
                $exception->getMessage(),
                ['exception' => $exception]
            );

            return $exception->getMessage();
        } catch (Exception $exception) {
            $message = ($exception->getMessage()) ? $exception->getMessage() : 'mautic.sms.transport.messagewhiz.not_configured';
            $this->logger->warning(
                $message,
                ['exception' => $exception]
            );

            return $message;
        }
    }

    /**
     * @param string $number
     *
     * @return string
     *
     * @throws NumberParseException
     */
    private function sanitizeNumber($number)
    {
        $number = str_replace(' ', '', $number);
        $number = str_replace('-', '', $number);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $number);
    }

    /**
     * @throws ConfigurationException
     */
    private function configureClient()
    {
        if ($this->client) {
            // Already configured
            return;
        }

        $this->senderId = $this->configuration->getSenderId();
        $this->client             = new Messagewhiz($this->configuration->getApiKey(),$this->configuration->getCampaignId(),$this->logger);
    }
}
