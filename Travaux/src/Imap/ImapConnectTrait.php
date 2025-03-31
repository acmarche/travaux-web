<?php

namespace AcMarche\Travaux\Imap;

use Symfony\Component\HttpFoundation\Request;

trait ImapConnectTrait
{
    /**
     * @throws \Exception
     */
    public function connectImap(Request $request): void
    {
        $user = $this->getUser();
        $passwordCrypted = $request->getSession()->get('imap_password');
        if(!$passwordCrypted) {
            throw new \Exception('Missing password');
        }
        $passwordDecrypted = $this->cryptoHelper->decrypt($passwordCrypted);
        $this->imapHandler->mailbox($user->getUserIdentifier(), $passwordDecrypted);
    }
}