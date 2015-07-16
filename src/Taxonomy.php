<?php
namespace Pangaea;

/**
 * Produces a JSON file of category data for Walmarts global e-commerce platform, Pangaea
 * It's assumed the order of array elements within each category doesn't matter, since it's JSON
 */
class Taxonomy
{
    private $items = [];

    /**
     * Adds the category into the taxonomy
     *
     * @param Category $item
     */
    public function addCategory(Category $item)
    {
        $this->items[] = $item;
    }

    /**
     * Build and validate the JSON.
     *
     * @return string
     * @throws PangaeaException
     */
    protected function build()
    {
        $json = json_encode($this->items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PangaeaException(json_last_error_msg());
        }

        return $json;
    }

    /**
     * Get the raw JSON output.
     *
     * @return string
     * @throws PangaeaException
     */
    public function getJson()
    {
        return $this->build();
    }

    /**
     * Validates the document and saves to the specified path
     *
     * @param $path
     * @throws PangaeaException
     */
    public function save($path)
    {
        file_put_contents($path, $this->build());
    }
}
