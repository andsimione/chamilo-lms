<?php

/*
 * This file is part of the PHPExifTool package.
 *
 * (c) Alchemy <support@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPExiftool\Driver\Tag\MPEG;

use JMS\Serializer\Annotation\ExclusionPolicy;
use PHPExiftool\Driver\AbstractTag;

/**
 * @ExclusionPolicy("all")
 */
class AudioBitrate extends AbstractTag
{

    protected $Id = 'Bit16-19';

    protected $Name = 'AudioBitrate';

    protected $FullName = 'MPEG::Audio';

    protected $GroupName = 'MPEG';

    protected $g0 = 'MPEG';

    protected $g1 = 'MPEG';

    protected $g2 = 'Audio';

    protected $Type = '?';

    protected $Writable = false;

    protected $Description = 'Audio Bitrate';

    protected $Index = 'mixed';

}