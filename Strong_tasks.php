<?php
	function CheckCurlResponse($i_code)
	{
  			$i_code=(int)$i_code;
  			$errors=array(
    			301=>'Moved permanently',
			    400=>'Bad request',
			    401=>'Unauthorized',
			    403=>'Forbidden',
			    404=>'Not found',
			    500=>'Internal server error',
			    502=>'Bad gateway',
			    503=>'Service unavailable'
  				);
			
			try
			{
			    #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
			    if($i_code!=200 && $i_code!=204)
			      throw new Exception(isset($errors[$i_code]) ? $errors[$i_code] : 'Undescribed error',$i_code);
			 }
			 catch(Exception $E)
			 {
			    die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
			 }
	}


	function amoCRM_auth($subdomain, $user)
	{
		   	#Формируем ссылку для запроса
   			$link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';
   			$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
    		#Устанавливаем необходимые опции для сеанса cURL
		    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		    curl_setopt($curl,CURLOPT_URL,$link);
		    curl_setopt($curl,CURLOPT_POST,true);
		    curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($user));
		    curl_setopt($curl,CURLOPT_HEADER,false);
		    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		     
		    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную

		    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера

		    curl_close($curl); #Завершаем сеанс cURL
            
		    CheckCurlResponse($code);
		    /**
		     * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
		     * нам придётся перевести ответ в формат, понятный PHP
		     */
		    $Response=json_decode($out,true);
		    
		    $Response=$Response['response'];

		    if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
		      return true;
		      else return false;

	}
    
    function amoCRM_getLeadsWithTasks($subdomain)
    {
	//запрашиваем список задач
		    $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/list'; #$subdomain уже объявляли выше
		    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
		    #Устанавливаем необходимые опции для сеанса cURL
		    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		    curl_setopt($curl,CURLOPT_URL,$link);
		    curl_setopt($curl,CURLOPT_HEADER,false);
		    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		     
		    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		    curl_close($curl);
		    CheckCurlResponse($code);
		    //echo 'CheckCurlResponse';
		    /**
		     * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
		     * нам придётся перевести ответ в формат, понятный PHP
		     */
		    $Response=json_decode($out,true);
		    $tasks=$Response['response']['tasks'];

		    foreach ($tasks as $task) // ищем невыполненные задачи, привязанные к сделкам
		    {
		   		
		   		if (($task['element_type']=='2') and !(in_array($task['element_id'],$leadsWithTasks)) and ($task['status']=='0'))
		   		{
		   			$leadsWithTasks[]=$task['element_id']; //записываем неповторяющийся ранее номер сделки в массив
		   			
		   		} 
		    }
		    return $leadsWithTasks;
	}
 	
 	function amoCRM_getTasksForCreate($subdomain, $i_leadsWithTasks) //получаем список сделок, у которых нет задач
 	{
 			
 			$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/leads/'; #$subdomain уже объявляли выше
		    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
		    #Устанавливаем необходимые опции для сеанса cURL
		    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		    curl_setopt($curl,CURLOPT_URL,$link);
		    curl_setopt($curl,CURLOPT_HEADER,false);
		    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		     
		    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		    curl_close($curl);
		    CheckCurlResponse($code);
		    
		    /**
		     * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
		     * нам придётся перевести ответ в формат, понятный PHP
		     */
		    $Response=json_decode($out,true);
		    $leads=$Response['response']['leads'];
            $myTime=604800+time();//текущий момент+7дней в секундах
            foreach ($leads as $lead) { //читаем все сделки. обрабатываем только те, которых нет в заполненном ранее массиве со сделками с задачами
            	
            	if (!(in_array($lead['id'], $i_leadsWithTasks))) {
         
            		$tasksForCreate['request']['tasks']['add'][]=array( 'element_id'=>$lead['id'],   //пишем в JSON сделки без задач или с выполненными задачами 
            			                                                'element_type'=>'2',
            			                                                'task_type'=>'4',
            			                                                'text'=>'Сделка без задачи',
            			                                                'responsible_user_id'=>$lead['responsible_user_id'],
            			                                                'complete_till'=>$myTime,
            			                                                'status'=>'0',
            			                                               );
            		

            	}
            }
            return $tasksForCreate;
 	}

 	function amoCRM_createTasks($subdomain, $i_tasksForCreate) //создаем задачи для сделок без задач
 	{
            $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';

		    $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
		    #Устанавливаем необходимые опции для сеанса cURL
		    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		    curl_setopt($curl,CURLOPT_URL,$link);
		    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
		    curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($i_tasksForCreate));
		    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		    curl_setopt($curl,CURLOPT_HEADER,false);
		    curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		    
		    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		    curl_close($curl);
		    CheckCurlResponse($code);
            
		    $Response=json_decode($out,true);
			$Response=$Response['response']['tasks']['add'];

			$output='ID добавленных задач:'.PHP_EOL;
			foreach($Response as $v)
			  if(is_array($v)) 
			  {
			    $output.=$v['id'].PHP_EOL;
			    $i++;
			  }
			//echo '<br>Добавлено задач к сделкам:' . $i . '<br>';
			return $output;
 	}

 	//основной блок
 	
 	$myDomain=''; //нужно указать поддомен зарегистрированной системы
 	$myUser=array(
     			'USER_LOGIN'=>'', #Ваш логин (электронная почта)
    			'USER_HASH'=>'' #Хэш для доступа к API (смотрите в профиле пользователя)
    		);
    
 	if (amoCRM_auth($myDomain, $myUser))   //аутентификация
 	{
 		$myLeadsWithTasks=amoCRM_getLeadsWithTasks($myDomain); //получаем список сделок с невыполненными задачами
 		
 		$myTasksForCreate=amoCRM_getTasksForCreate($myDomain, $myLeadsWithTasks); //получаем список задач с прописанными сделками
 		
 		amoCRM_createTasks($myDomain, $myTasksForCreate); //создаем задачи
 	} //else не пишем. Если будет ошибка, о ней будет сообщено функцией CheckCurlResponse
 
 	
?>
