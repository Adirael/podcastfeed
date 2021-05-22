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
        $this->title = $this->getValue($data, 'title');
        $this->subtitle = $this->getValue($data, 'subtitle');
        $this->summary = $this->getValue($data, 'summary', null, false);
        // $this->description = strip_tags($this->getValue($data, 'description', null, true));
        $this->description = $this->getValue($data, 'description', null, true);
        $this->content_encoded = $this->getValue($data, 'content_encoded', null, true);
        $this->link = $this->getValue($data, 'link', null, false);
        $this->pubDate = $this->getValue($data, 'publish_at');
        $this->url = $this->getValue($data, 'url');
        $this->guid = $this->getValue($data, 'guid');
        $this->type = $this->getValue($data, 'type');
        $this->duration = $this->getValue($data, 'duration');
        $this->explicit = $this->getValue($data, 'explicit');
        $this->author = $this->getValue($data, 'author');
        $this->feed_season = $this->getValue($data, 'feed_season');
        $this->feed_episode = $this->getValue($data, 'feed_episode');
        $this->image = $this->getValue($data, 'image');
        $this->length = $this->getValue($data, 'length');
        $this->isPermaLink = $this->getValue($data, 'isPermaLink');

        // Ensure publish date is a DateTime instance
        if (is_string($this->pubDate)) {
            $this->pubDate = new DateTime($this->pubDate);
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

        // Create the <description>

        // // FOR SPOTIFY
        // // DESCRIPTION WITHOUT HTML
        //
        // // $description = $dom->createElement("description");
        // // $description->appendChild($dom->createCDATASection($this->description));
        // // $item->appendChild($description);
        //
        //
        // // DESCRIPTION CON SUMARIO
        // $description = $dom->createElement("description", $this->summary);
        // $item->appendChild($description);
        //
        // $content_encoded = $dom->createElement("content:encoded");
        // $content_encoded->appendChild($dom->createCDATASection($this->description));
        // $item->appendChild($content_encoded);

        // PARA SPOTIFY
        // DESCRIPTION CON HTML

        // $description = $dom->createElement("description");
        // $description->appendChild($dom->createCDATASection($this->description));
        // $item->appendChild($description);

        // SPOTIFY
        /*
        
        if(empty($this->content_encoded)) {

          // Create the <itunes:summary>
          $itune_summary = $dom->createElement("itunes:summary", $this->summary);
          $item->appendChild($itune_summary);

          $description = $dom->createElement("description", $this->summary);
          $item->appendChild($description);

          // FEED TRADICIONAL
        } else {
          // Create the <itunes:subtitle>
          if (!empty($this->subtitle)) {
              $itune_subtitle = $dom->createElement("itunes:subtitle", $this->subtitle);
              $item->appendChild($itune_subtitle);
          }

          // Create the <itunes:summary>
          $itune_summary = $dom->createElement("itunes:summary", $this->summary);
          $item->appendChild($itune_summary);

          $description = $dom->createElement("description");
          $description->appendChild($dom->createCDATASection($this->description));
          $item->appendChild($description);

          $content_encoded = $dom->createElement("content:encoded");
          $content_encoded->appendChild($dom->createCDATASection($this->description));
          $item->appendChild($content_encoded);
        }
        
        */
     
        if(!empty($this->description)) {
          $description = $dom->createElement("description");
          $description->appendChild($dom->createCDATASection($this->description));
          $item->appendChild($description);
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
        if ($this->link) {
            // Create the <link>
            $link = $dom->createElement("link", $this->link);
            $item->appendChild($link);
        }

        if ($this->feed_season > 0) {
            $feed_season = $dom->createElement("itunes:season", intval($this->feed_season));
            $item->appendChild($feed_season);
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
