<?php

class erLhcoreClassChatStatistic {


    /**
     * Gets pending chats
     */
    public static function getTopTodaysOperators($limit = 100, $offset = 0)
    {
    	$time = (time()-(24*3600));

    	$SQL = 'SELECT lh_chat.user_id,count(lh_chat.id) as assigned_chats FROM lh_chat WHERE time > :time AND user_id > 0 GROUP BY user_id';

    	$db = ezcDbInstance::get();
    	$stmt = $db->prepare($SQL);
    	$stmt->bindValue( ':time',$time,PDO::PARAM_INT);
    	$stmt->setFetchMode(PDO::FETCH_ASSOC);
    	$stmt->execute();
    	$rows = $stmt->fetchAll();

    	$usersID = array();
    	foreach ($rows as $item) {
    		$usersID[] = $item['user_id'];
    	}

    	if ( !empty($usersID) ) {
    		$users = erLhcoreClassModelUser::getUserList(array('limit' => $limit,'filterin' => array('id' => $usersID)));
    	}

    	$usersReturn = array();
    	foreach ($rows as $row) {
    		$usersReturn[$row['user_id']] = $users[$row['user_id']];
    		$usersReturn[$row['user_id']]->statistic_total_chats = $row['assigned_chats'];
    		$usersReturn[$row['user_id']]->statistic_total_messages = erLhcoreClassChat::getCount(array('filtergte' => array('time' => $time),'filter' => array('user_id' => $row['user_id'])),'lh_msg');
    		
    		$usersReturn[$row['user_id']]->statistic_upvotes = erLhcoreClassChat::getCount(array('filtergte' => array('time' => $time),'filter' => array('fbst' => 1,'user_id' => $row['user_id'])));
    		$usersReturn[$row['user_id']]->statistic_downvotes = erLhcoreClassChat::getCount(array('filtergte' => array('time' => $time),'filter' => array('fbst' => 2,'user_id' => $row['user_id'])));
    	}

    	return $usersReturn;
    }

}

?>