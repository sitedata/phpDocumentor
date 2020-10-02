<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Descriptor\Builder\Reflector\Tags;

use phpDocumentor\Descriptor\Builder\AssemblerReducer;
use phpDocumentor\Descriptor\Builder\Reflector\AssemblerAbstract;
use phpDocumentor\Descriptor\Builder\Reflector\Docblock\DescriptionAssemblerReducer;
use phpDocumentor\Descriptor\Tag\DeprecatedDescriptor;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;

/**
 * Constructs a new descriptor from the Reflector for an `{@}deprecated` tag.
 *
 * This object will read the reflected information for the `{@}deprecated` tag and create a {@see DeprecatedDescriptor}
 * object that can be used in the rest of the application and templates.
 */
class DeprecatedAssembler extends BaseTagAssembler
{
    /**
     * Creates a new Descriptor from the given Reflector.
     *
     * @param Deprecated $data
     */
    public function buildDescriptor(object $data) : DeprecatedDescriptor
    {
        $descriptor = new DeprecatedDescriptor($data->getName());
        $descriptor->setVersion($data->getVersion() ?: '');

        return $descriptor;
    }
}
