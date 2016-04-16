<?php
namespace nochso\WriteMe\Placeholder;

use nochso\WriteMe\Interfaces\Placeholder;

class Badge extends AbstractPlaceholder
{
    public function getIdentifier()
    {
        return 'badge';
    }

    public function getCallPriorities()
    {
        return [Placeholder::PRIORITY_FIRST];
    }

    /**
     * @param \nochso\WriteMe\Placeholder\Call $call
     * @param string                           $imageUrl URL to a badge image.
     * @param string                           $altText  Alternative text for image.
     * @param string|null                      $url      Optional URL the image will link to. If null, no link will
     *                                                   be created.
     */
    public function image(Call $call, $imageUrl, $altText, $url = null)
    {
        $badge = sprintf('![%s](%s)', $altText, $imageUrl);
        if ($url !== null) {
            $badge = sprintf('[%s](%s)', $badge, $url);
        }
        $call->replace($badge);
    }

    /**
     * Badge creation via shields.io.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     * @param string                           $subject Subject to the left of the badge.
     * @param string                           $status  Status to the right of the badge.
     * @param string                           $color   Optional status color. Defaults to lightgrey. Can be any hex
     *                                                  color, e.g. `0000FF` or one of the following: brightgreen,
     *                                                  green, yellowgreen, yellow, orange, red, lightgrey or blue.
     * @param string|null                      $altText Optional alternative text for image. Defaults to
     *                                                  `subject - status`.
     * @param string|null                      $url     Optional URL the badge will link to. If null, no link will be
     *                                                  created.
     */
    public function badge(Call $call, $subject, $status, $color = 'lightgrey', $altText = null, $url = null)
    {
        // Escaping for shields.io GET parameters
        $e = function ($s) {
            $s = str_replace('-', '--', $s);
            return rawurlencode($s);
        };
        $imageUrl = sprintf('https://img.shields.io/badge/%s-%s-%s.svg', $e($subject), $e($status), $e($color));
        if ($altText === null) {
            $altText = $subject . ' - ' . $status;
        }
        $this->image($call, $imageUrl, $altText, $url);
    }

    /**
     * Bonus badge for mentioning writeme.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     */
    public function badgeWriteme(Call $call)
    {
        //[![write me to read me](https://img.shields.io/badge/writeme-readme-7787b2.svg)](https://github.com/nochso/writeme)
        $this->badge($call, 'writeme', 'readme', 'blue', 'write me to read me', 'https://github.com/nochso/writeme');
    }

    /**
     * Travis CI build status.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     * @param string|null                      $userRepository User/repository, e.g. `nochso/writeme`. Defaults to `composer.name`
     * @param string|null                      $branch         Optional branch name.
     */
    public function badgeTravis(Call $call, $userRepository = null, $branch = null)
    {
        if ($userRepository === null) {
            $userRepository = $call->getDocument()->getFrontmatter()->get('composer.name');
        }
        if ($userRepository === null) {
            return;
        }
        $image = sprintf('https://api.travis-ci.org/%s.svg', $userRepository);
        if ($branch !== null) {
            $image .= '?branch=' . $branch;
        }
        $url = 'https://travis-ci.org/' . $userRepository;
        $this->image($call, $image, 'Travis CI build status', $url);
    }

    public function badgeLicense(Call $call, $userRepository = null)
    {
        $image = sprintf('https://img.shields.io/github/license/%s.svg', $userRepository);
        $url = 'https://packagist.org/packages/' . $userRepository;
        $this->image($call, $image, 'License', $url);
    }

    /**
     * scrutinizer.
     *
     * @param \nochso\WriteMe\Placeholder\Call $call
     * @param null                             $userRepository Github user/repository.
     * @param null                             $branch
     */
    public function badgeScrutinizer(Call $call, $userRepository = null, $branch = null)
    {
        $image = sprintf('https://scrutinizer-ci.com/g/%s/badges/quality-score.png', $userRepository);
        $url = sprintf('https://scrutinizer-ci.com/g/%s/', $userRepository);
        if ($branch !== null) {
            $image .= '?b=' . $branch;
            $url .= '?b=' . $branch;
        }
        $this->image($call, $image, 'Scrutinizer code quality', $url);
    }

    public function badgeCoveralls(Call $call, $userRepository = null, $branch = null)
    {
        $image = sprintf('https://coveralls.io/repos/github/%s/badge.svg', $userRepository);
        $url = sprintf('https://coveralls.io/github/%s', $userRepository);
        if ($branch !== null) {
            $image .= '?branch=' . $branch;
            $url .= '?branch=' . $branch;
        }
        $this->image($call, $image, 'Coverage status', $url);
    }

    public function badgeTag(Call $call, $userRepository = null)
    {
        $image = sprintf('https://img.shields.io/github/tag/%s.svg', $userRepository);
        $url = sprintf('https://github.com/%s/tags', $userRepository);
        $this->image($call, $image, 'Latest tag on Github', $url);
    }
}
