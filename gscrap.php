<?php
/*
*I don't own this code
* Real author JUSTIN ( http://google-scraper.squabbel.com/ )
* I converted their code into a class for my project
*/
class gscrap{
    //Variables    
    public $isProxyAvailable = false;
    public $pwd="14071d5e0b3c2d14f5efdd9559d5dea5";  // Your www.seo-proxies.com API password
    public $uid=7059;                                   // Your www.seo-proxies.com API userid
    // The  main keyword and additional sub keywords for the scraping
    public $main_keyword="";              // The main keyword
    public $extra_keywords=""; // alternatives to mix in to receive more than the average 1000 results from Google
    public $show_html=1;                             // Output either for console or in html for a website (0 / 1)
    public $max_results=10; 
    public $showAll=1;
    private $page=0;
    private $PROXY=array();                                                     // after rotate api call it has the elements: [address](proxy host),[port](proxy port),[external_ip](the external IP),[ready](0/1)
    private $results=array();
    private $NL;
    private $HR;
    private $B;
    private $B_;
    private $content="";
    private $ch=NULL;
    //Variable functions
    public function initHtmlVar() {
        if ($this->show_html) $this->NL="<br>\n"; else $this->NL="\n";
        if ($this->show_html) $this->HR="<hr>\n"; else $this->HR="---------------------------------------------------------------------------------------------------\n";
        if ($this->show_html) $this->B="<b>"; else $this->B="";
        if ($this->show_html) $this->B_="</b>"; else $this->B_="";
    } 
    private function run() {
        $this->initHtmlVar();
        if ($this->show_html)
        {
            $this->content.="<html><body>";
        }        
        $keywords=explode(",",$this->extra_keywords);
        $this->content.= "$this->NL$this->B Scraping max. $this->max_results results for the main keyword \"$this->main_keyword\" using ".count($keywords)." additional keywords $this->B_ $this->NL$this->NL";
        foreach($keywords as $keyword)
        {
            if ($this->max_results<=0) break;
            $search_string=urlencode($this->main_keyword." ".$keyword);
            // force new curl session    
            $this->content.="$this->NL";
            $this->content.="===========================================================================================================================$this->NL";
            $this->content.="Scraping for \"$this->main_keyword $keyword\" $this->NL";
            $this->content.="===========================================================================================================================$this->NL";
            $this->content.="$this->NL";        
        $runs=0; 
        if($this->isProxyAvailable)       
            $res=$this->proxy_api("rotate");
        else
            $res=1;
        $ip=$this->getip();
        if ($res <= 0)
        {
            $this->content.="Error: Proxy API connection failed (Error $res).$this->NL$this->NL$this->NL";
            sleep(2);
            break;
        } 
        else
        {
            if($this->isProxyAvailable)
            $this->content.="API: Received proxy IP $this->PROXY[external_ip] on port $this->PROXY[port]$this->NL";
        }
        $this->ch=$this->new_curl_session($this->ch);
        $errors=0;
        $ip=$this->getip();
        //Scrap through google results
        while (1)
        {
            if ($this->max_results<=0) break;
                $runs++;
            $this->content.="Run $runs \t Page $this->page \t loading$this->NL";
            if ((!$ip) || ($ip == ""))
                $ip=$this->getip($ch); 
            if ((!$ip) || ($ip == ""))     // If the proxy didn't work: rotate to next proxy
            {
                if($this->isProxyAvailable) {
                    $this->content.="Proxy is not working, rotating ..$this->NL";            
                    $res=$this->proxy_api("rotate");                    
                }
                else {
                    $res=1;
                }
                $ip=$this->getip($this->ch);
                if ($res <= 0)
                {
                    $this->content.="Error: API connection failed (Error $res), retry.$this->NL$this->NL$this->NL";
                    sleep (10);
                    continue;
                } 
                else
                {
                    if($this->isProxyAvailable) 
                    $this->content.="API: Received proxy IP $this->PROXY[external_ip] on port $this->PROXY[port]$this->NL";
                }            
                $this->ch=$this->new_curl_session($this->ch);
                continue;
            }
            $this->content.="Current tested IP-Address: $ip$this->NL$this->NL";
            $google_ip="www.google.com"; // hidden potential left
            if ($this->page == 0)
            {
                // we imitate a firefox browser search and will query for 100 results
                $url="http://$google_ip/search?q=$search_string&amp;ie=utf-8&as_qdr=all&amp;aq=t&amp;rls=org:mozilla:us:official&amp;client=firefox&num=100";
            } 
            else
            {
                $num=$this->page*100;
                $url="http://$google_ip/search?q=$search_string&ie=utf-8&as_qdr=all&aq=t&rls=org:mozilla:us:official&client=firefox&start=$num&num=100";
            }
            $this->content.="Search URL: $url$this->NL";        
            curl_setopt ($this->ch, CURLOPT_URL, $url);
            $htmdata = curl_exec ($this->ch);
            $newtry=0;  
            if (!$htmdata)
            {
                $error = curl_error($ch);
                $info = curl_getinfo($ch);        
                $this->content.="\tError browsing: $error [ $info ]$this->NL";
                sleep (3);
                $newtry=1;
            }
            if (strstr($htmdata,"computer virus or spyware application")) 
            {
                $this->content.="Captcha error is popping up ! We need more proxies !";
                die();
                $newtry=1;
            }
            if (strstr($htmdata,"entire network is affected")) 
            {
                $this->content.="Google blocked us, we need more proxies !$this->NL";
                die();
                $newtry=1;
            }   
            if (strstr($htmdata,"http://www.download.com/Antivirus")) 
            {
                $this->content.="Google blocked us, we need more proxies !$this->NL";
                die();
                $newtry=1;
            } 
            if ($newtry)
            {
                if ($errors++ > 3)
                {
                    $this->content.="Abort: too many google errors! $this->NL$this->NL";
                    sleep(5);
                    break;
                }
                if($this->isProxyAvailable)
                $res=$this->proxy_api("rotate");
                else
                $res=1;                
                $ip=$this->getip();
                if ($res <= 0)
                {
                    $this->content.="Error: API connection failed (Error $res), retry.$this->NL$this->NL$this->NL";
                    sleep (10);
                } 
                else
                {
                    if($this->isProxyAvailable)
                    $this->content.="API: Received proxy IP $this->PROXY[external_ip] on port $this->PROXY[port]$this->NL";
                }
                $this->content.="Rotated IP and retrying$this->NL";
                $this->ch=$this->new_curl_session($this->ch);
                continue;
            }
            $skip=0;
        // now we test if (more) results are available
            if (strstr($htmdata,"/images/yellow_warning.gif"))
            {
                $this->content.="No (more) results left$this->NL";
                $skip=1;
            } 
            if (!$skip)
            {
                $len=strlen($htmdata);
                $this->content.="\t Received $len bytes$this->NL";
            // Now we parse the html content, putting it into a DOM tree
                $dom = new domDocument; 
                $dom->strictErrorChecking = false; 
                $dom->preserveWhiteSpace = true; 
                @$dom->loadHTML($htmdata); 
                $lists=$dom->getElementsByTagName('li'); 
                $num=0;            
                foreach ($lists as $list)
                {
                    unset($ar);unset($divs);unset($div);unset($cont);unset($result);unset($tmp);
                    $result['main_keyword']=$this->main_keyword;
                    $result['sub_keyword']=$keyword;
                    $ar=$this->dom2array_full($list);              
                    if (count($ar) < 2) 
                    {
                        $this->content.="S";
                        continue; // skipping advertisement and similar spam
                    }
                    if ((!isset($ar['class'])) || ($ar['class'] != 'g')) 
                    {
                        $this->content.="?";
                        continue; // skipping non-search results
                    }
                    // adaption to new google layout
                    //if ($num==2)var_dump($ar);
                    //if ($num==3)var_dump($ar);
                    if (isset($ar['div'][1]))
                        $ar['div']=&$ar['div'][0];
                    if (isset($ar['div'][1]))
                        $ar['div']=&$ar['div'][0];
                    //$ar=&$ar['div']['span']; // Google removed the span
                    //$ar=&$ar['div']; // change 2012-2013, commented out again
                    // adaption finished
                    $divs=$list->getElementsByTagName('div');
                    $div=$divs->item(1);
                    $cont="";
                    $cont=$this->getContent($cont,$div); 
                    $num++;
                    $result['title']=&$ar['h3']['a']['textContent'];
                    $tmp=strstr($ar['h3']['a']['@attributes']['href'],"http");
                    $result['url']=$tmp;
                    if (strstr($ar['h3']['a']['@attributes']['href'],"interstitial")) echo "!";           
                    $tmp=parse_url($result['url']);
                    $result['host']=$tmp['host'];
                    if (strstr($cont,"<b >...</b><br >")) // remove some dirt behind the description
                    {
                        $result['desc']=substr($cont,0,strpos($cont,"<b >...</b><br >"));
                    } else
                    if (strstr($cont,"<cite")) // remove some dirt behind the description in case the description was short
                    {
                        $result['desc']=substr($cont,0,strpos($cont,"<span class='f'><cite"));
                    } 
                    else
                        $result['desc']=$cont;
            
                    $this->content.="$this->B Result parsed:$this->B_ $result[title]$this->NL";
                    flush();                    
                    $results[]=$result; // This adds the result to our large result array
                    if (!--$this->max_results) break;
            }
         }//end of !skip
         // Test if more results are available
        $next=0;
        if (!$skip)
        {
            $tables=$dom->getElementsByTagName('table');
            if (strstr($htmdata,"Next</a>")) $next=1;
            else
            {
                $needstart=($this->page+1)*100;
                $findstr="start=$needstart";
                if (strstr($htmdata,$findstr)) $next=1;
            }
            $this->page++;
        }
        if (!$next)
        {
            $this->content.="Finished $runs runs on current search, last google page was $this->page$this->NL";
            break;
        } 
           
      }//end of while
     }//end of foreach
     return $results;
   }  //end of function
   public function getData() {
       $results = $this->run();           
       $data="";
       $data.="$this->NL$this->NL";
       $data.="$this->B Scraping of keywords finished$this->B_ $this->NL";
       foreach ($results as $result)
        {
            $data.=$this->HR;
            $data.="$this->B Keyword:$this->B_ $result[main_keyword] $result[sub_keyword]$this->NL";
            $data.="$this->B Host:$this->B_ $result[host]$this->NL";
            $data.="$this->B URL:$this->B_ $result[url]$this->NL";
            $data.="$this->B Title:$this->B_ $result[title]$this->NL";
            $data.="$this->B Desc:$this->B_ $result[desc]$this->NL";
            $data.=$this->NL;
        }
        if ($this->show_html)
        {
            $data.="</body></html>";
        }
        if($this->showAll) {
            $this->content.=$data;            
            return $this->content;
        }
        else
            return $data;
   }
    //CURL and IP FUNCTIONS
    function getip()
    {
            if($this->isProxyAvailable){                
                if (!$this->PROXY['ready']) return -1; // proxy not ready
            }
            
            $curl_handle=curl_init();
            curl_setopt($curl_handle,CURLOPT_URL,'http://squabbel.com/ipxx.php'); // this site will return the plain IP address, great for testing if a proxy is ready
            curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($curl_handle,CURLOPT_TIMEOUT,10);
            curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
            if($this->isProxyAvailable){
            $curl_proxy = "$this->PROXY[address]:$this->PROXY[port]";
            curl_setopt($curl_handle, CURLOPT_PROXY, $curl_proxy);
            }
            $tested_ip=curl_exec($curl_handle);
            
              if(preg_match("^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}^", $tested_ip))
              {
                    curl_close($curl_handle);
                    return $tested_ip;
              }
              else
              {
                $info = curl_getinfo($curl_handle);
                curl_close($curl_handle);
                return 0; // possible error would be a wrong authentication IP
              }
    }

    function new_curl_session($ch=NULL)
    {
        if($this->isProxyAvailable) {
            if ((!isset($this->PROXY['ready'])) || (!$this->PROXY['ready'])) return $ch; // proxy not ready
        }        
        if (isset($ch) && ($ch != NULL)) 
              curl_close($ch);
              $ch = curl_init();
              curl_setopt ($ch, CURLOPT_HEADER, 0);
              curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
              curl_setopt ($ch, CURLOPT_RETURNTRANSFER , 1);
              if($this->isProxyAvailable) {
                  $curl_proxy = "$this->PROXY[address]:$this->PROXY[port]";
                  curl_setopt($ch, CURLOPT_PROXY, $curl_proxy);
              }
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
              curl_setopt($ch, CURLOPT_TIMEOUT, 20);
              curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en; rv:1.9.0.4) Gecko/2009011913 Firefox/3.0.6");
              return $ch;
    }

    function proxy_api($cmd,$x="")
    {     
        $fp = fsockopen("www.seo-proxies.com", 80);
        if (!$fp) 
        {
            echo "Unable to connect to proxy API $this->NL";
            return -1; // connection not possible
        } else 
        {
            if ($cmd == "rotate")
            {
                $this->PROXY['ready']=0;
                fwrite($fp, "GET /api.php?api=1&uid=$uid&pwd=$pwd&cmd=rotate&randomness=1 HTTP/1.0\r\nHost: www.seo-proxies.com\r\nAccept: text/html, text/plain, text/*, */*;q=0.01\r\nAccept-Encoding: plain\r\nAccept-Language: en\r\n\r\n");
                stream_set_timeout($fp, 8);
                $res="";
                $n=0;
                while (!feof($fp)) 
                {
                    if ($n++ > 4) break;
                    $res .= fread($fp, 8192);
                }
                $info = stream_get_meta_data($fp);
                fclose($fp);
            
                if ($info['timed_out']) 
                {
                    echo 'API: Connection timed out! $this->NL';
                    return -2; // api timeout
              } else 
                {
                    if (strlen($res) > 1000) return -3; // invalid api response (check the API website for possible problems)
                    $data=extractBody($res);
                    $ar=explode(":",$data);
                    if (count($ar) < 4) return -100; // invalid api response
                    switch ($ar[0])
                    {
                        case "ERROR":
                            echo "API Error: $res $this->NL";
                            return 0; // Error received
                        break;
                        case "ROTATE":
                            $this->PROXY['address']=$ar[1];
                            $this->PROXY['port']=$ar[2];
                            $this->PROXY['external_ip']=$ar[3];
                            $this->PROXY['ready']=1;
                            return 1;
                        break;
                        default:
                            echo "API Error: Received answer $ar[0], expected \"ROTATE\"";
                            return -101; // unknown API response
                    }
                }
            } // cmd==rotate
        }
    }

    //CONTENT EXTRACTION FUNCTIONS
    function extractBody($response_str)
    {
        $parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
        if (isset($parts[1])) return $parts[1];
        return '';
    }

    function dom2array($node) 
    {
      $res = array();
      if($node->nodeType == XML_TEXT_NODE)
      {
        $res = $node->nodeValue;
      } else
      {
        if($node->hasAttributes())
        {
            $attributes = $node->attributes;
            if(!is_null($attributes))
            {
                $res['@attributes'] = array();
                foreach ($attributes as $index=>$attr) 
                {
                    $res['@attributes'][$attr->name] = $attr->value;
                }
            }
        }
        if($node->hasChildNodes())
        {
            $children = $node->childNodes;
            for($i=0;$i<$children->length;$i++)
            {
                $child = $children->item($i);
                $res[$child->nodeName] = $this->dom2array($child);
            }
            $res['textContent']=$node->textContent;
        }
      }
      return $res;
    }

    function getContent($NodeContent="",$nod)
    {    
        $NodList=$nod->childNodes;
        for( $j=0 ;  $j < $NodList->length; $j++ )
        { 
            $nod2=$NodList->item($j);
            $nodemane=$nod2->nodeName;
            $nodevalue=$nod2->nodeValue;
            if($nod2->nodeType == XML_TEXT_NODE)
                $NodeContent .= $nodevalue;
            else
            {     $NodeContent .= "<$nodemane ";
               $attAre=$nod2->attributes;
               foreach ($attAre as $value)
                  $NodeContent .= "{$value->nodeName}='{$value->nodeValue}'" ;
                $NodeContent .= ">";                    
                $this->getContent($NodeContent,$nod2);                    
                $NodeContent .= "</$nodemane>";
            }
        }
        return $NodeContent;
       
    }

    function dom2array_full($node)
    {
        $result = array();
        if($node->nodeType == XML_TEXT_NODE) 
        {
            $result = $node->nodeValue;
        } else 
        {
            if($node->hasAttributes()) 
            {
                $attributes = $node->attributes;
                if((!is_null($attributes))&&(count($attributes))) 
                    foreach ($attributes as $index=>$attr) 
                    $result[$attr->name] = $attr->value;
            }
            if($node->hasChildNodes())
            {
                $children = $node->childNodes;
                for($i=0;$i<$children->length;$i++) 
                {
                    $child = $children->item($i);
                    if($child->nodeName != '#text')
                    if(!isset($result[$child->nodeName]))
                        $result[$child->nodeName] = $this->dom2array($child);
                    else 
                    {
                        $aux = $result[$child->nodeName];
                        $result[$child->nodeName] = array( $aux );
                        $result[$child->nodeName][] = $this->dom2array($child);
                    }
                }
            }
        }
        return $result;
    } 
 
}
?>