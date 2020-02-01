<?php


namespace FahrradKruken\YAWP\GTheme;


class Thumbnail
{
    /**
     * @param array       $imgArray - ACF image array
     * @param string      $mobileSize - Size required on mobile
     * @param string|bool $defaultSize - Size required on desktop
     *
     * @return string - Needed image size URL
     */
    public function acfImg($imgArray = [], $mobileSize = 'medium', $defaultSize = 'large')
    {
        return wp_is_mobile() ?
            $imgArray['sizes'][$mobileSize] :
            (!$defaultSize ? $imgArray['url'] : $imgArray['sizes'][$defaultSize]);
    }

    /**
     * @param int|\WP_Post $post
     * @param string       $mobileSize
     * @param string       $defaultSize
     *
     * @return false|string
     */
    public function postImg($post, $mobileSize = 'medium', $defaultSize = 'large')
    {
        return wp_is_mobile() ?
            get_the_post_thumbnail_url($post, $mobileSize) :
            get_the_post_thumbnail_url($post, $defaultSize);
    }

    /**
     * @param int    $wpImgID - WordPress Image ID
     * @param string $mobileSize
     * @param string $defaultSize
     *
     * @return false|string
     */
    public function img($wpImgID, $mobileSize = 'medium', $defaultSize = 'large')
    {
        return wp_is_mobile() ?
            wp_get_attachment_image_url($wpImgID, $mobileSize) :
            wp_get_attachment_image_url($wpImgID, $defaultSize);
    }
}