<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_update extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;
    
    // do not update if user isn't active (didn't connect)
    if ($u->active)
    {      
      // update the user nickname timestamp
      $cmd =& pfcCommand::Factory("updatemynick");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);
      $cmd->run($xml_reponse, $clientid, $param, $sender, NULL, NULL);

      // get other online users on each channels
      $cmd =& pfcCommand::Factory("getonlinenick");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);

      // get new message posted on each channels
      $cmd =& pfcCommand::Factory("getnewmsg");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);

      // take care to disconnect timeouted users on the server
      $container =& $c->getContainerInstance();
      $disconnected_users = $container->removeObsoleteNick(NULL,$c->timeout); 
      // if whould be possible to echo these disconnected users on a server tab
      // server tab is not yet available so I just commente the code
      //       foreach ($disconnected_users as $u)
      //       {
      //         $cmd =& pfcCommand::Factory("notice");
      //         $cmd->run($xml_reponse, $clientid, _pfc("%s quit (timeout)",$u), $sender, $recipient, $recipientid, 2);
      //       }

      // do not send a response in order to save some bandwidth
      //      $xml_reponse->addScript("pfc.handleResponse('update', 'ok', '');");
    }
    else
      $xml_reponse->addScript("pfc.handleResponse('update', 'ko', '');");

  }
}

?>