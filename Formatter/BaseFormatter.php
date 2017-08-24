<?php

namespace IrishDan\NotificationBundle\Formatter;

use IrishDan\NotificationBundle\Exception\MessageFormatException;
use IrishDan\NotificationBundle\Message\Message;
use IrishDan\NotificationBundle\Notification\NotificationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class BaseFormatter
{
    protected $twig;
    protected $eventDispatcher;

    public function setTemplating(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function renderTwigTemplate($data, $user, $template)
    {
        return $this->twig->render(
            $template,
            [
                'data' => $data,
                'user' => $user,
            ]
        );
    }

    public function format(NotificationInterface $notification)
    {
        if (!empty($this->twig) && $notification->getTemplate()) {
            $data = $notification->getDataArray();
            $data['body'] = $this->renderTwigTemplate(
                $data,
                $notification->getNotifiable(),
                $notification->getTemplate()
            );

            $notification->setDataArray($data);
        }
    }

    protected static function createMessage($dispatchData, $messageData, $channel = 'default')
    {
        $message = new Message();

        $message->setChannel($channel);
        $message->setDispatchData($dispatchData);
        $message->setMessageData($messageData);

        return $message;
    }

    protected static function createMessagaData(array $notificationData)
    {
        // Build the message data array.
        $messageData = [];
        $messageData['body'] = empty($notificationData['body']) ? '' : $notificationData['body'];
        $messageData['title'] = empty($notificationData['title']) ? '' : $notificationData['title'];

        return $messageData;
    }

    protected static function createFormatterException($shouldImplement, $type)
    {
        $message = sprintf('Notifiable must implement %s interface in order to format as a %s message',
            $shouldImplement, $type);
        throw new MessageFormatException(
            $message
        );
    }
}