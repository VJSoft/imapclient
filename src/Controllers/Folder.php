<?php

namespace Vjsoft\Imapclient\Controllers;

class Folder
{
    protected $client;

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

    /**
     * folder is a container, not a mailbox. cannot open it.
     */
    public $no_select;

    public $marked;

    public $has_children;

    public $referal;

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
}