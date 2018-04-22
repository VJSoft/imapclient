<?php

namespace Vjsoft\Imapclient\Controllers;

class Folder
{
    public $client;

    public $path;

    public $name;

    public $fullName;

    public $children = [];

    public $delimiter;

    /**
     * folder can't containg any "children".
     * No CreateFolder method on this folder.
     */
    public $no_inferiors;

    public $no_select;    // folder is a container, not a mailbox. cannot open it.

    public $marked;

    public $has_children;

    public $referal;

    public $imap_status;

    public $folderData;


//--------------------------------------------------------------------------------
    public function __construct(Client $client, $folder)
    {
        $this->client = $client;

        $this->delimiter = $folder->delimiter;
        $this->path = $folder->name;
        $this->fullName = $this->decodeName($folder->name);
        $this->name = $this->getSimpleName($this->delimiter, $this->fullName);

        $this->parseAttributes($folder->attributes);

    }

    public function setFolderData(){

        $this->client->openFolder($this);
        $this->folderData = imap_check($this->client->connection);
    }


    public function hasChildren()
    {
        return $this->has_children;
    }

    public function setChildren($children = [])
    {
        $this->children = $children;
    }

    public function getMessages($criteria = 'ALL')
    {
        return $this->client->getMessages($this, $criteria);
    }

    protected function decodeName($name)
    {
        preg_match('#\{(.*)\}(.*)#', $name, $preg);
        return mb_convert_encoding($preg[2], "UTF-8", "UTF7-IMAP");
    }

    protected function getSimpleName($delimiter, $fullName)
    {
        $arr = explode($delimiter, $fullName);

        return end($arr);
    }

    protected function parseAttributes($attributes)
    {
        $this->no_inferiors = ($attributes & LATT_NOINFERIORS)  ? true : false;
        $this->no_select    = ($attributes & LATT_NOSELECT)     ? true : false;
        $this->marked       = ($attributes & LATT_MARKED)       ? true : false;
        $this->referal      = ($attributes & LATT_REFERRAL)     ? true : false;
        $this->has_children = ($attributes & LATT_HASCHILDREN)  ? true : false;
    }

    /*
     * getMessagesList() - NEW method:  utilizes imap_fetch_overview() function, instead of imap_search
     *                                  taking only the message info, without setting it as read while only displaying the message list
     */
    public function getMessagesList($messagesPerPage = 10, $page = 1){

        $this->setFolderData();

        $msgCount = $this->folderData->Nmsgs;
        if(!$msgCount) return [];

        $listEnd = $msgCount - ($page-1)*$messagesPerPage;
        $listStart = $listEnd-$messagesPerPage+1;  //todo: implement pagination
        $listStart = max([1,$listStart]);

        /*
         * we take the last portion of the messages list - they are the newest.
         * The portion size should be a parameter, related to pagination - depends how many message u want to display on the page
         */
        $availableMessages = imap_fetch_overview($this->client->connection, "$listStart:$msgCount", 0);
        //$availableMessages = imap_fetch_overview($this->client->connection, "1:10", 0);

        /*
         * Then we reverse the array so the newest are on top
         */
        return array_reverse($availableMessages);
    }

 /*
  * method appendToSentFolder()
  * Since IMAP protocol does not support send message function, the user usually implements sending by
  * external code.
  * This method adds the message sent by another handler to the Sent Folder of the mailbox
  */
//    public function appendToFolder($message){
//        imap_append($this->client, $this->path, $message);
//    }

}