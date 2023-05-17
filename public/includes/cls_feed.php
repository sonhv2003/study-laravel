<?php
/**
 * rss_feed (simple rss 2.0 feed creator php class)
 *
 * @author     Christos Pontikis http://pontikis.net
 * @copyright  Christos Pontikis
 * @license    MIT http://opensource.org/licenses/MIT
 * @version    0.1.0 (28 July 2013)
 *
 */
class rss_feed  {

  /**
   * Constructor
   *
   * @param array $a_db database settings
   * @param string $xmlns XML namespace
   * @param array $a_channel channel properties
   * @param string $site_url the URL of your site
   * @param string $site_name the name of your site
   * @param bool $full_feed flag for full feed (all topic content)
   */
  public function __construct($xmlns, $a_channel, $site_url, $site_name, $full_feed = false) {
    // initialize
    $this->xmlns = ($xmlns ? ' ' . $xmlns : '');
    $this->channel_properties = $a_channel;
    $this->site_url = $site_url;
    $this->site_name = $site_name;
    $this->full_feed = $full_feed;
  }


  /**
   * Generate RSS 2.0 feed
   *
   * @return string RSS 2.0 xml
   */
  public function outputRSS($xml_items = '') {

    $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";

    $xml .= '<rss version="2.0"' . $this->xmlns . '>' . "\n";

    // channel required properties
    $xml .= '<channel>' . "\n";
    $xml .= '<title>' . $this->channel_properties["title"] . '</title>' . "\n";
    $xml .= '<link>' . $this->channel_properties["link"] . '</link>' . "\n";
    $xml .= '<description>' . $this->channel_properties["description"] . '</description>' . "\n";

    // channel optional properties
    if(array_key_exists("language", $this->channel_properties)) {
      $xml .= '<language>' . $this->channel_properties["language"] . '</language>' . "\n";
    }
    if(array_key_exists("image_title", $this->channel_properties)) {
      $xml .= '<image>' . "\n";
      $xml .= '<title>' . $this->channel_properties["image_title"] . '</title>' . "\n";
      $xml .= '<link>' . $this->channel_properties["image_link"] . '</link>' . "\n";
      $xml .= '<url>' . $this->channel_properties["image_url"] . '</url>' . "\n";
      $xml .= '</image>' . "\n";
    }

    $xml .= $xml_items;

    $xml .= '</channel>'. "\n";

    $xml .= '</rss>';

    return $xml;
  }


}