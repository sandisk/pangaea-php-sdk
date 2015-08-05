<?php
namespace Pangaea;

/**
 * Produces an XML feed of product data for Walmarts global e-commerce platform, Pangaea
 *
 * String concat isn’t ideal, but, well, XML sucks and life is short - this was quick and preserves our sanity ;-)
 */
class Feed
{
    /**
     * Timestamp
     *
     * @var string
     */
    private $timestamp;

    /**
     * Items XML
     *
     * @var string
     */
    private $items;

    /**
     * Creates a new feed document, for the specified timestamp (defaults to now if empty)
     *
     * @param $timestamp
     */
    public function __construct($timestamp = null)
    {
        $this->timestamp = Date::format($timestamp);
    }

    /**
     * Renders the XML specific to an item, then adds it into the feed
     *
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $this->items .= $item->render();
    }

    // hardcoded values could be passed via a `header()` function, but only if needed - aren't right now!
    private function render()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Feed xmlns="http://walmart.com/">
    <Header>
        <!-- Other than `feedDate`, Header elements are hardcoded (sources not been specified) -->
        <version>1.4</version>
        <sellerId>59</sellerId>
        <tenant>gm.asda.com</tenant>
        <locale>en_GB</locale>
        <feedDate>{$this->timestamp}</feedDate>
        <feedType>UNCATEGORIZEDITEM</feedType>
        <batchId>1</batchId>
        <transactionId>ITEMFULL_1_0_21188745_2</transactionId>
        <requestSource>FUSION</requestSource>
    </Header>
    {$this->items}
</Feed>
XML;
    }

    /**
     * Get the feed as raw XML.
     *
     * @return string
     */
    public function getXml()
    {
        return $this->render();
    }

    /**
     * Validates the document and saves to the specified path
     *
     * @param $path
     * @return boolean
     * @throws \PangaeaException
     */
    public function save($path)
    {
        $result = file_put_contents($path, $this->render());

        Xml::validate($path, __DIR__ . '/../xsd/feed/Feed.xsd');

        return $result > 0;
    }
}
