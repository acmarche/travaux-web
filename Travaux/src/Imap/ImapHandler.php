<?php

namespace AcMarche\Travaux\Imap;

use Carbon\Carbon;
use DirectoryTree\ImapEngine\Collections\MessageCollection;
use DirectoryTree\ImapEngine\Mailbox;
use DirectoryTree\ImapEngine\MessageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ImapHandler
{
    private ?Mailbox $mailbox = null;

    public function __construct(
        #[Autowire(env: "IMAP_HOST")]
        private readonly string $imapHost,
    ) {
    }

    public function mailbox(string $username, string $password): ?Mailbox
    {
        $this->mailbox = new Mailbox([
            'port' => 993,
            'username' => $username,
            'password' => $password,
            'encryption' => 'ssl',
            'host' => $this->imapHost,
            //'debug' => true,
        ]);
        return $this->mailbox;
    }

    public function isConnected(): bool
    {
        return $this->mailbox->connected();
    }

    /**
     * @param int $days
     * @return MessageCollection
     * @throw ImapConnectionFailedException
     */
    public function messages(int $days = 7): MessageCollection
    {
        $inbox = $this->mailbox->inbox();

        return $inbox->messages()
            ->since(Carbon::now()->subDays($days))
            ->withHeaders()
            ->get();
    }

    public function message(string $uid): ?MessageInterface
    {
        $inbox = $this->mailbox->inbox();

        return $inbox->messages()
            ->withHeaders()
            ->withBody()
            ->find($uid);

    }

}