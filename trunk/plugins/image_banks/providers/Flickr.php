<?php
namespace ImageBanks;

class Flickr extends Provider
    {
    protected $id                = 2;
    protected $name              = "Flickr";
    protected $download_endpoint = "https://farm";
    
    protected $configs = array(
        "flickr_api_key" => "4833d9129594f20fa87ff16cd6455447",
        "flickr_secret" => "ea6f900891d1a0d0"
    );
    protected $warning = "";


    public function getId()
        {
        return $this->id;
        }

    public function getName()
        {
        return $this->name;
        }

    public function getAllowedDownloadEndpoint()
        {
        return $this->download_endpoint;
        }


    static function checkDependencies()
        {
        return function_exists('curl_version');
        }

    public function buildConfigPageDefinition(array $page_def)
        {
        $page_def[] = \config_add_section_header($this->name);
        $page_def[] = \config_add_text_input('flickr_api_key', $this->lang["image_banks_pixabay_api_key"]);
        return $page_def;
        }

    public function runSearch($keywords, $per_page = 24, $page = 1)
        {
        if($per_page < 3)
            {
            $per_page = 3;
            }
        else if($per_page > 200)
            {
            $per_page = 200;
            }

        if($page < 1)
            {
            $page = 1;
            }

        $search_hash = md5("{$this->configs["flickr_api_key"]}--{$keywords}--{$per_page}--{$page}");
        $api_cached_results = $this->getCache($search_hash, 24);
        //lldebug($api_cached_results,'Cached Results');
        if(!$api_cached_results)
            {
            $api_results = $this->searchFlickr($keywords, $per_page, $page);
            //lldebug($api_results,'Results');
            $search_results = json_decode($api_results, true);

            if(isset($search_results["stat"]) and $search_results["stat"]=='fail')
                {
                $provider_error = new ProviderSearchResults();
                $provider_error->setError($search_results["message"]);

                return $provider_error;
                }

            $this->setCache($search_hash, $api_results);
            }

        if(!isset($search_results))
            {
            $search_results = json_decode($api_cached_results, true);
            }

        $provider_results = new ProviderSearchResults();
        /*
        Photo Source URLs
        You can construct the source URL to a photo once you know its ID, server ID, farm ID and secret, 
        as returned by many API methods.
        The URL takes the following format:
        https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}.jpg
            or
        https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{secret}_[mstzb].jpg
            or
        https://farm{farm-id}.staticflickr.com/{server-id}/{id}_{o-secret}_o.(jpg|gif|png)

        * Before November 18th, 2011 the API returned image URLs with hostnames like: "farm{farm-id}.static.flickr.com". Those URLs are still supported.
        Size Suffixes
        The letter suffixes are as follows:
        s	small square 75x75
        q	large square 150x150
        t	thumbnail, 100 on longest side
        m	small, 240 on longest side
        n	small, 320 on longest side
        -	medium, 500 on longest side
        z	medium 640, 640 on longest side
        c	medium 800, 800 on longest side†
        b	large, 1024 on longest side*
        h	large 1600, 1600 on longest side†
        k	large 2048, 2048 on longest side†
        o	original image, either a jpg, gif or png, depending on source format

        * Before May 25th 2010 large photos only exist for very large original images.
        † Medium 800, large 1600, and large 2048 photos only exist after March 1st 2012.
        */
        if(!isset($search_results["photos"])) return $provider_results;

        foreach($search_results["photos"]["photo"] as $result)
            {
            if(isset($result['originalsecret'])) $original_file_url = sprintf("https://farm%s.staticflickr.com/%s/%s_%s_o.%s",$result['farm'],$result['server'],$result['id'],$result['originalsecret'],$result['originalformat']);
            else $original_file_url = sprintf("https://farm%s.staticflickr.com/%s/%s_%s_k.jpg",$result['farm'],$result['server'],$result['id'],$result['secret']);
            $provider_result = new \ImageBanks\ProviderResult($result["id"], $this);
            $provider_result
                ->setTitle($result['title'])
                ->setOriginalFileUrl($original_file_url)
                ->setProviderUrl($original_file_url)
                ->setPreviewUrl(sprintf("https://farm%s.staticflickr.com/%s/%s_%s_m.jpg",$result['farm'],$result['server'],$result['id'],$result['secret']))
                ->setPreviewWidth(0)
                ->setPreviewHeight(0);

            $provider_results[] = $provider_result;
            }

        if($this->warning != "")
            {
            $provider_results->setWarning($this->warning);

            $this->warning = "";
            }

        $provider_results->total = count($provider_results);
        if(isset($search_results["photos"]["total"]))
            {
            $provider_results->total = $search_results["photos"]["total"];
            }

        return $provider_results;
        }


    private function searchFlickr($keywords, $per_page = 24, $page = 1)
        {
        $flickr_api_url = generateURL(
            "https://www.flickr.com/services/rest/",
            array(
                "method"   => "flickr.photos.search",
                "api_key"  => $this->configs["flickr_api_key"],
                "extras"   => "original_format",
                "text"     => $keywords,
                "per_page" => $per_page,
                "page"     => $page,
                "format"   => "json",
                "nojsoncallback" => 1
            )
        );

        $curl_handle = curl_init();
        $curl_response_headers = array();
        //lldebug($flickr_api_url,'URL');

        curl_setopt($curl_handle, CURLOPT_URL, $flickr_api_url);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curl_handle,
            CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$curl_response_headers)
                {
                $length = strlen($header);
                $header = explode(':', $header, 2);

                // Invalid header
                if(count($header) < 2)
                    {
                    return $length;
                    }

                $name = strtolower(trim($header[0]));

                if(!array_key_exists($name, $curl_response_headers))
                    {
                    $curl_response_headers[$name] = array(trim($header[1]));
                    }
                else
                    {
                    $curl_response_headers[$name][] = trim($header[1]);
                    }

                return $length;
                }
        );

        $result = curl_exec($curl_handle);
        //lldebug($result,'RESULT');
        //lldebug(curl_getinfo($curl_handle),'RESULT INFO');
        //lldebug(curl_error($curl_handle),'RESULT ERROR');
        $response_status_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        curl_close($curl_handle);

        $result_json_decoded = json_decode($result, true);
        //lldebug($result_json_decoded,'DECODED RESULT');

        if(
            $response_status_code != 200
            || ($response_status_code == 200 && $result_json_decoded["photos"]["total"] == 0)
        )
            {
            switch($response_status_code)
                {
                case 200:
                    $message = $this->lang["image_banks_try_something_else"];
                    break;

                case 429:
                    $message = $this->lang["image_banks_try_again_later"];
                    break;

                default:
                    $message = $result;
                    break;
                }

            $error_data = array(
                "error" => array(
                    "message"  => $message
                )
            );

            return json_encode($error_data);
            }

        if(isset($curl_response_headers["x-ratelimit-remaining"][0]) && $curl_response_headers["x-ratelimit-remaining"][0] <= 20)
            {
            $warning_message = str_replace(
                array(
                    "%PROVIDER",
                    "%RATE-LIMIT-REMAINING",
                    "%TIME"
                ),
                array(
                    $this->name,
                    $curl_response_headers["x-ratelimit-remaining"][0],
                    date("i:s", $curl_response_headers["x-ratelimit-reset"][0])
                ),
                $this->lang["image_banks_warning_rate_limit_almost_reached"]
            );

            $this->warning = $warning_message;
            }

        return $result;
        }
    }