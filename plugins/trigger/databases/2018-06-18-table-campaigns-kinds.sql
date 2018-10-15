/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  ducta
 * Created: Jun 18, 2018
 */

CREATE TABLE `tblcampaigns_kinds` (
  `id` int(11) NOT NULL,
  `kind` varchar(100) NOT NULL,
  `kind_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `comment` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;