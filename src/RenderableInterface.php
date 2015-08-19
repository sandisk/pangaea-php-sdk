<?php
namespace Pangaea;

interface RenderableInterface
{
    /**
     * Render the object. Should return XML as a string.
     *
     * @return string
     */
    public function render();
}
