<?php

class GovnokodRuAuthorModel {
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var string
     */
    public $name;
}

class GovnokodRuCommentModel {
    /**
     * @var GovnokodRuAuthorModel
     */
    public $author;

    /**
     * @var GovnokodRuCommentModel[]
     */
    public $comments = array();

    /**
     * @var string
     */
    public $text;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $parent_id;

    /**
     * @var string
     */
    public $date_gmt;

    /**
     * @var int
     */
    public $post_id;
}

class GovnokodRuPostModel {
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $description;

    /**
     * @var GovnokodRuAuthorModel
     */
    public $author;

    /**
     * @var bool
     */
    public $has_comments;

    /**
     * @var GovnokodRuCommentModel
     */
    public $comments = array();

    /**
     * @var string
     */
    public $date_gmt;

    /**
     * @var string
     */
    public $url;
}