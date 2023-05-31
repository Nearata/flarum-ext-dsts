# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.4.0] - 2023-05-31

- bump php to 8.0
- bump flarum to 1.7
- update dependencies
- feat: made fof/upload work within other options
- feat: ability to enable/disable options
- feat: bbcode permission-based, no longer toggable
- feat: if bbcode is found in a post, all other options will be ignored

## [2.3.0] - 2022-09-07

- Add support for BBCode.
- Detect CommentPost only.
- Add fof/upload support. If this option is enabled, the other options will be ignored.
- Add ability to hide only the first post or the whole discussion.
- Add ability to style the warning message by target `.nearata-dsts.hidden`

## [2.2.0] - 2022-08-30

- Check if tag is restricted. [[Fix #4](https://github.com/Nearata/flarum-ext-dsts/issues/4)]
- Allow guests to see the content by tag. [Requires flarum/tags]

## [2.1.0] - 2021-06-26

- Make permissions tag scoped.

## [2.0.0] - 2021-06-20

- Update to Flarum 1.0

## [1.0.0] - 2021-04-28

First release.
