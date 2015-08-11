<?php
namespace Pangaea;

interface RenderableInterface
{
    /**
     * Render XML and return as a string.
     *
     * @return string
     */
    public function render();
}
