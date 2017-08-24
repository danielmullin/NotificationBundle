<?php

namespace IrishDan\NotificationBundle\Formatter;

use IrishDan\NotificationBundle\Exception\MessageFormatException;
use IrishDan\NotificationBundle\Notification\NotificationInterface;
use IrishDan\NotificationBundle\PusherableInterface;
use IrishDan\NotificationBundle\PusherManager;

class PusherDataFormatter extends BaseFormatter implements MessageFormatterInterface
{
    const CHANNEL = 'pusher';
    protected $pusherConfiguration;
    protected $pusherManager;

    public function __construct(PusherManager $pusherManager)
    {
        $this->pusherManager = $pusherManager;
    }

    public function format(NotificationInterface $notification)
    {
        $notification->setChannel(self::CHANNEL);
        parent::format($notification);

        /** @var PusherableInterface $notifiable */
        $notifiable = $notification->getNotifiable();
        if (!$notifiable instanceof PusherableInterface) {
            $this->createFormatterException(PusherableInterface::class, self::CHANNEL);
        }

        // Build the dispatch data array.
        $dispatchData = [
            'channel' => $this->pusherManager->getUserChannelName($notifiable),
            'event' => $this->pusherManager->getEvent(),
        ];

        $messageData = self::createMessagaData($notification->getDataArray());
        $message = self::createMessage($dispatchData, $messageData, self::CHANNEL);

        return $message;
    }
}