CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `admin` int(1) NOT NULL,
  `theme` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

