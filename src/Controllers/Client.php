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

    //public $folders;


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

            if ($orderPosition = array_search($folder->name, config('imap')['order'])){

//                if ($hierarchical && $folder->hasChildren()) {
//                    $pattern = $folder->name.$folder->delimiter.'%';
//
//                    $children = $this->getFolders(true, $pattern);
//                    $folder->setChildren($children);
//                }
                $folder->imap_status = imap_status($this->connection, $folder->path, SA_ALL);

                $folders[$orderPosition] = $folder;
            }
        }

        ksort($folders);
   //     $this->folders = $folders;

        return $folders;
    }


    public function openFolder(Folder $folder) //todo: tuj da wzema da go razkaram
    {
        $this->checkConnection();

        if ($this->activeFolder != $folder) {
            $this->activeFolder = $folder;
            try {
                imap_reopen($this->connection, $folder->path, $this->getOptions(), 3);

            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                throw new GetMessagesFailedException($errorMessage);
            }

        }
    }

    public function getFolderByName($folderName)
    {
//        $folderNames = [];
//        foreach ($this->folders as $key=>$folder){
//            $folderNames[$key] = $folder->name;
//        }
//
//        return $this->folders[array_search($folderName, $folderNames)];
//        $key = array_search($folderName, $this->folders);
//        if ($key)
        $folders = $this->getFolders(false);
        foreach($folders as $folder)
        {
            if ($folder->name == $folderName) {
                return $folder;
            }
        }
        return false;
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

    public function deleteMessage($uid){
        $this->checkConnection();
        return imap_delete($this->connection, $uid, FT_UID);
    }
}
