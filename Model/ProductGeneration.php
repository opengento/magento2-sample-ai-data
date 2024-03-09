<?php

namespace Opengento\SampleAiData\Model;

class ProductGeneration
{
    public function generate($prompt, $maxProducts, $category, $descriptionLength)
    {
        var_dump("Generating products for " . $prompt . " with a maximum of " . $maxProducts . " products in the " . $category . " category with a maximum description length of " . $descriptionLength . " characters.");
    }
}
