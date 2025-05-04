<?php namespace Torann\PodcastFeed;

use DateTime;
use DOMDocument;

class Media
{
    /**
     * Title of media.
     *
     * @var string
     */
    private $title;

    /**
     * Subtitle of media.
     *
     * @var string|null
     */
    private $subtitle;

    /**
     * URL to the media web site.
     *
     * @var string
     */
    private $link;

    /**
     * Date of publication of the media.
     *
     * @var DateTime
     */
    private $pubDate;

    /**
     * description media.
     *
     * @var string
     */
    private $description;

    /**
     * summary media.
     *
     * @var string
     */
    private $summary;

    /**
     * URL of the media
     *
     * @var string
     */
    private $url;

    /**
     * Type of media (audio / mpeg, for example).
     *
     * @var string
     */
    private $type;

    /**
     * Author of the media.
     *
     * @var string
     */
    private $author;

    /**
     * GUID of the media.
     *
     * @var string
     */
    private $guid;

    /**
     * GUID isPermaLink attribute
     *
     * @var string
     */
    private $isPermaLink;

    /**
     * Duration of the media only as HH:MM:SS, H:MM:SS, MM:SS or M:SS.
     *
     * @var string
     */
    private $duration;

    /**
     * Explicit flag of the media.
     *
     * @var string
     */
    private $explicit;

    /**
     * URL to the image representing the media.
     *
     * @var string
     */
    private $image;

    /**
     * Length in bytes of the media file.
     *
     * @var string
     */
    private $length;

    /**
     * Class constructor
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->title        = $this->getValue($data, 'title');
        $this->subtitle     = $this->getValue($data, 'subtitle');
        $this->summary      = $this->getValue($data, 'summary');
        $this->description  = $this->getValue($data, 'description', null, true);
        // $this->description = strip_tags($this->getValue($data, 'description', null, true));
        // $this->content_encoded = $this->getValue($data, 'content_encoded', null, true);
        $this->link         = $this->getValue($data, 'link', null, false);
        $this->pubDate      = $this->getValue($data, 'publish_at');
        $this->url          = $this->getValue($data, 'url');
        $this->guid         = $this->getValue($data, 'guid');
        $this->type         = $this->getValue($data, 'type');
        $this->duration     = $this->getValue($data, 'duration');
        $this->explicit     = $this->getValue($data, 'explicit');
        $this->author       = $this->getValue($data, 'author');
        $this->person       = $this->getValue($data, 'person');
        $this->feed_season  = $this->getValue($data, 'feed_season');
        $this->feed_episode = $this->getValue($data, 'feed_episode');
        $this->feed_type    = $this->getValue($data, 'feed_type');
        $this->image        = $this->getValue($data, 'image');
        $this->length       = $this->getValue($data, 'length');
        $this->isPermaLink  = $this->getValue($data, 'isPermaLink');
        $this->transcription= $this->getValue($data, 'transcription');
        $this->subtitles    = $this->getValue($data, 'subtitles');
        $this->subtitles_vtt= $this->getValue($data, 'subtitles_vtt');
        $this->chapters     = $this->getValue($data, 'chapters');
        $this->chapters_vtt = $this->getValue($data, 'chapters_vtt');
        $this->plc_chapters = isset($data['plc_chapters']) ? $data['plc_chapters'] : false;

        // Ensure publish date is a DateTime instance
        if (is_string($this->pubDate)) {
            $this->pubDate  = new DateTime($this->pubDate);
        }
    }

    /**
     * Get value from data and escape it.
     *
     * @param  mixed  $data
     * @param  string $key
     * @param  mixed $default
     *
     * @return string
     */
    public function getValue($data, $key, $default = null, $raw = false)
    {
        $value = array_get($data, $key, $default);

        if($key == 'categories' OR $key == 'links' OR $key == 'person') {
          return $value;
        }

        if(!$raw) {
            return htmlspecialchars($value);
        }

        return $value;
    }

    /**
     * Get media publication date.
     *
     * @return  DateTime
     */
    public function getPubDate()
    {
        return $this->pubDate;
    }

    /**
     * Adds media in the DOM document setting.
     *
     * @param DOMDocument $dom
     */
    public function addToDom(DOMDocument $dom)
    {
        // Recovery of  <channel>
        $channels = $dom->getElementsByTagName("channel");
        $channel = $channels->item(0);

        // Create the <item>
        $item = $dom->createElement("item");
        $channel->appendChild($item);

        // Create the <title>
        $title = $dom->createElement("title", $this->title);
        $item->appendChild($title);

        if(!empty($this->description)) {
          $description = $dom->createElement("description");
          $description->appendChild($dom->createCDATASection($this->description));
          $item->appendChild($description);
        }

        if(!empty($this->summary)) {
          $summary = $dom->createElement("summary", $this->summary);
          $item->appendChild($summary);
        }

        if(!empty($this->content_encoded)) {
          $content_encoded = $dom->createElement("content:encoded");
          $content_encoded->appendChild($dom->createCDATASection($this->content_encoded));
          $item->appendChild($content_encoded);
        }

        // Create the <pubDate>
        $pubDate = $dom->createElement("pubDate", $this->pubDate->format(DATE_RFC2822));
        $item->appendChild($pubDate);

        // Create the <enclosure>
        $enclosure = $dom->createElement("enclosure");
        $enclosure->setAttribute("url", $this->url);
        $enclosure->setAttribute("type", $this->type);
        $enclosure->setAttribute("length", $this->length);
        $item->appendChild($enclosure);

        // Create the author
        if ($this->author) {
            // Create the <author>
            $author = $dom->createElement("author", $this->author);
            $item->appendChild($author);

            // Create the <itunes:author>
            $itune_author = $dom->createElement("itunes:author", $this->author);
            $item->appendChild($itune_author);
        }

        // Create the author
        if ($this->person) {
            foreach($this->person as $person) {
                $name = '';

                if(isset($person['full_name']) && !empty($person['full_name'])) {
                    $name = $person['full_name'];
                } elseif(isset($person['name'])) {
                    $name = $person['name'];
                }

                if(empty($name)) {
                    continue;
                }

                $p = $dom->createElement("podcast:person", $name);

                if(isset($person['picture']) && !empty($person['picture'])) {
                    $p->setAttribute("img",$person['picture']);
                }
                if(isset($person['href']) && !empty($person['href'])) {
                    $p->setAttribute("href",$person['href']);
                }

                $item->appendChild($p);
            }
        }

        if ($this->link) {
            // Create the <link>
            $link = $dom->createElement("link", $this->link);
            $item->appendChild($link);
        }

        if ($this->feed_season > 0) {
            $feed_season = $dom->createElement("itunes:season", intval($this->feed_season));
            $item->appendChild($feed_season);
        }

        $feed_type = $dom->createElement("itunes:episodeType", (($this->feed_type == 'bonus' OR $this->feed_type == 'trailer') ? $this->feed_type : 'full'));
        $item->appendChild($feed_type);

        if ($this->transcription) {
            $transcription = $dom->createElement("podcast:transcript");
            $transcription->setAttribute("type","plain/txt");
            $transcription->setAttribute("url",$this->transcription);
            $item->appendChild($transcription);
        }

        if ($this->subtitles) {
            $subtitles = $dom->createElement("podcast:transcript");
            $subtitles->setAttribute("type","application/x-subrip");
            $subtitles->setAttribute("rel","captions");
            $subtitles->setAttribute("url",$this->subtitles);
            $item->appendChild($subtitles);

            $subtitles = $dom->createElement("podcast:transcript");
            $subtitles->setAttribute("type","text/srt");
            $subtitles->setAttribute("rel","captions");
            $subtitles->setAttribute("url",$this->subtitles);
            $item->appendChild($subtitles);
        }

        if ($this->subtitles_vtt) {
            $subtitles = $dom->createElement("podcast:transcript");
            $subtitles->setAttribute("type","text/vtt");
            $subtitles->setAttribute("rel","captions");
            $subtitles->setAttribute("url",$this->subtitles_vtt);
            $item->appendChild($subtitles);
        }

        if ($this->chapters) {
            $chapters = $dom->createElement("podcast:chapters");
            $chapters->setAttribute("type","application/json+chapters");
            $chapters->setAttribute("url",$this->chapters);
            $item->appendChild($chapters);
        }

        if ($this->chapters_vtt) {
            $subtitles = $dom->createElement("podcast:chapters");
            $subtitles->setAttribute("type","text/vtt");
            $subtitles->setAttribute("rel","captions");
            $subtitles->setAttribute("url",$this->chapters_vtt);
            $item->appendChild($subtitles);
        }

        if ($this->plc_chapters && is_array($this->plc_chapters) && count($this->plc_chapters) > 0) {
            $plc_chapters = $dom->createElement("psc:chapters");
            $plc_chapters->setAttribute("version","1.2");
            $plc_chapters->setAttribute("xmlns:psc","http://podlove.org/simple-chapters");

            array_unshift($this->plc_chapters,[
                "startTime" => 0,
                "title" => "Inicio"
            ]);

            /*
            $this->plc_chapters[] = [
                "startTime" => $this->duration,
                "title" => "Final"
            ];
            */

            foreach($this->plc_chapters as $plc_chapter) {
                $new = $dom->createElement("psc:chapter");
                $new->setAttribute("start",$plc_chapter['startTime']);
                $new->setAttribute("title",$plc_chapter['title']);
                $plc_chapters->appendChild($new);
            }
            $item->appendChild($plc_chapters);
        }

        if ($this->feed_episode > 0) {
            $feed_episode = $dom->createElement("itunes:episode", intval($this->feed_episode));
            $item->appendChild($feed_episode);
        }

        // Create the <itunes:duration>
        $itune_duration = $dom->createElement("itunes:duration", $this->duration);
        $item->appendChild($itune_duration);

        // Create the <itunes:explicit>
        $explicit = $dom->createElement("itunes:explicit", (is_null($this->explicit) OR !$this->explicit OR empty($this->explicit) OR $this->explicit == 'no') ? 'clean' : 'yes');
        $item->appendChild($explicit);

        // Create the <guid>
        $guid = $dom->createElement("guid", $this->guid);
        $guid->setAttribute("isPermaLink", $this->isPermaLink);
        $item->appendChild($guid);

        // Create the <itunes:image>
        if ($this->image) {
            $itune_image = $dom->createElement("itunes:image");
            $itune_image->setAttribute("href", $this->image);
            $item->appendChild($itune_image);
        }
    }
}
