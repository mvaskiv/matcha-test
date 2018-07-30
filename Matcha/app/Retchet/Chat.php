<?php
namespace App\Retchet;


use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Retchet\UserIndetify;


class Chat implements MessageComponentInterface {
    protected $clients;
    private $user;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->user = new UserIndetify;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $querystring = (explode('=', $conn->httpRequest->getUri()->getQuery()))[1];
        $this->user->addUser($querystring ,$conn->resourceId);

        echo "New connection! ({$conn->resourceId}) id ({$querystring})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $br = false;
        $rt = array();
        $token = "";

        $tmp = json_decode($msg, true);
        if (!$tmp || !($token = $this->user->checkInput($tmp))){
          $this->rt['status'] = 'ko';
          $this->rt['error'] = 'error';
          $from->send(json_encode($this->rt));
          return ;
        }
        $tmp = json_decode($msg, true);
        if (!($token =$this->user->checkInput($tmp))){
          $this->rt['status'] = 'ko';
          $this->rt['error'] = 'error';
          $from->send(json_encode($this->rt));
          return ;
        }else {
          if ($this->user->receiver($tmp['to'])){
            $this->rt['status'] = 'ok';
          }
          else{
            $this->rt['status'] = 'ko';
            $this->rt['error'] = 'receiver ofline';
            $from->send(json_encode($this->rt));
          }
          $this->rt['token'] = $token;
           $from->send(json_encode($this->rt));
        }
        //write message to database
        // if ($tmp['status'] == 'msg'){
        //     if (!$this->user->write_to_db($tmp['to'], $from->resourceId, $tmp['msg'])){
        //       $this->rt['status'] = 'ko';
        //       $this->rt['error'] = 'chat is imposible';
        //       $from->send(json_encode($this->rt));
        //       return ;
        //     }
        // }

        //send to user
        foreach($this->user->pool as $el){
          if ($el['id'] == $tmp['to']){
            foreach ($this->clients as $client) {
              if ($el['chat_id'] === $client->resourceId) {
                $client->send($tmp['msg']);
              }
            }
          }
       }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $this->user->dropUser($conn->resourceId);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
