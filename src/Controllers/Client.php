<?php

namespace Vjsoft\Imapclient\Controllers;

use Illuminate\Support\Facades\Config;

use Vjsoft\Imapclient\Exceptions\ConnectionFailedException;
use Vjsoft\Imapclient\Exceptions\GetMessagesFailedException;

class Client
{
 /*
  * connection should be public - doesnt work as protected
  */

    public $connection = false;

    /*
     *  Server details
     */
    public $host;

    public $port;

    public $encryption;

    public $validate_cert;


    /**
     * User credentials
     */
    public $username;

    public $password;


    /*
     * some
     */
    protected $read_only = false;

    protected $activeFolder = false;


// -----------------------------------------------


    public function __construct($config = [])
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->encryption = $config['encryption'];
        $this->validate_cert = $config['validate_cert'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    public function setReadOnly($readOnly = true)
    {
        $this->read_only = $readOnly;
    }

    public function isConnected()
    {
        return ($this->connection) ? true : false;
    }

    public function isReadOnly()
    {
        return $this->read_only;
    }

    public function checkConnection()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
    }

    public function connect($attempts = 3)
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }

        try {
            $this->connection = imap_open(
                $this->getAddress(),
                $this->username,
                $this->password,
                $this->getOptions(),
                $attempts
            );
        } catch (\ErrorException $e) {
            $message = $e->getMessage().'. '.implode("; ", imap_errors());

            throw new ConnectionFailedException($message);
        }

        return $this;
    }

    public function disconnect()
    {
        if ($this->isConnected()) {
            imap_close($this->connection);
        }

        return $this;
    }

    public function getFolders($hierarchical = true, $parent_folder = null)
    {
        $this->checkConnection();
        $folders = [];

        if ($hierarchical) {
            $pattern = $parent_folder.'%';
        } else {
            $pattern = $parent_folder.'*';
        }

        $items = imap_getmailboxes($this->connection, $this->getAddress(), $pattern);
        foreach ($items as $item) {
            $folder = new Folder($this, $item);

            if ($hierarchical && $folder->hasChildren()) {
                $pattern = $folder->fullName.$folder->delimiter.'%';

                $children = $this->getFolders(true, $pattern);  //todo: da checkna dali towa ne e bug?!
                $folder->setChildren($children);
            }
            $folder->imap_status = imap_status($this->connection, $folder->path, SA_ALL);

            $folders[] = $folder;
        }

        return $folders;
    }


    public function openFolder(Folder $folder) //todo: tuj da wzema da go razkaram
    {
        $this->checkConnection();

        if ($this->activeFolder != $folder) {
            $this->activeFolder = $folder;

            imap_reopen($this->connection, $folder->path, $this->getOptions(), 3);
        }
    }

    public function getFolderByName($folderName)
    {
        $folders = $this->getFolders(false);
        foreach($folders as $folder)
        {
            if ($folder->fullName == $folderName) {
                return $folder;
            }
        }
        return false;
    }
/*
 * getMessagesList() - NEW method:  utilizes imap_fetch_overview() function, instead of imap_search
 *                                  taking only the message info, without setting it as read while only displaying the message list
 */
    public function getMessagesList(Folder $folder){
        $this->checkConnection();

        try {
            $this->openFolder($folder);

            $folderData = imap_check($this->connection);
            $msgCount = $folderData->Nmsgs;
            $listStart = $msgCount-10;

            /*
             * we take the last portion of the messages list - they are the newest.
             * The portion size should be a parameter, related to pagination - depends how many message u want to display on the page
             */
            $availableMessages = imap_fetch_overview($this->connection, "$listStart:$msgCount", SE_UID);

            /*
             * Then we reverse the array so the newest are on top
             */
            return array_reverse($availableMessages,true);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            throw new GetMessagesFailedException($errorMessage);
        }

    }

    public function getMessages(Folder $folder, $criteria = 'ALL')
    {
        $this->checkConnection();

        try {
            $this->openFolder($folder);
            $messages = [];
            $availableMessages = imap_search($this->connection, $criteria, SE_UID);

            if ($availableMessages !== false) {
                foreach ($availableMessages as $msgno) {
                    $message = new Message($msgno, $this);

                    $messages[$message->message_id] = $message;
                }
            }
            return $messages;
        } catch (\Exception $e) {
            $message = $e->getMessage();

            throw new GetMessagesFailedException($message);
        }
    }

    protected function getOptions()
    {
        return ($this->isReadOnly()) ? OP_READONLY : 0;
    }

    protected function getAddress()
    {
        $address = "{".$this->host.":".$this->port."/imap";
        if (!$this->validate_cert) {
            $address .= '/novalidate-cert';
        }
        if ($this->encryption == 'ssl') {
            $address .= '/ssl';
        }
        $address .= '}';

        return $address;
    }
}
