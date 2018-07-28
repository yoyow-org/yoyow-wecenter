$(function()
{
    var site_id ;
	// 检测首页动态更新
	var checkactionsnew_handle = setInterval(function ()
	{
		check_actions_new(new Date().getTime());
	}, 60000);

	$('.aw-mod.side-nav a').click(function () {

		if ($('#main_title').attr('id') != null && $(this).attr('rel'))
		{
			$('.aw-mod.side-nav a').removeClass('active');

			$(this).addClass('active');

			window.location.hash = $(this).attr('rel');
			if(window.location.hash.indexOf('++') != -1 && window.location.hash !="#site_announce__list"){
                site_id = window.location.hash.split('++')[1];
                window.location.hash = window.location.hash.split('++')[0];
                $('#main_title').html("<b>公告详情</b>").find('i').detach();
			}else if(window.location.hash =="#site_announce__list"){
                $('#main_title').html("<b>全部公告</b>").find('i').detach();
			}else{
                $('#main_title').html($(this).html()).find('i').detach();
			}


			$('#main_contents').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');

			$('#bp_more').attr('data-page', 0).click();

			return false;
		}
	});


	$('#bp_more').click(function()
	{
		var _this = this;
        $('.mod-footer').show();
		switch (window.location.hash)
		{
			default:
				if (window.location.hash != '#all')
				{
					var query_string = window.location.hash.replace(/#/g, '').split('__');

					for (i = 0; i < 3; i++)
					{
						if (!query_string[i])
						{
							query_string[i] = '';
						}
					}

					if (query_string[1])
					{
						var cur_filter = query_string[1];
					}
					else
					{
						var cur_filter = '';
					}
				}
				else
				{
					var cur_filter = '';
				}

				var request_url = G_BASE_URL + '/home/ajax/index_actions/page-' + $(this).attr('data-page') + '__filter-' + cur_filter;
			break;

			case '#draft_list__draft':
				var request_url = G_BASE_URL + '/home/ajax/draft/page-' + $(this).attr('data-page');
				if($('#main_title .btn-success').length <=0)
				{
					$('#main_title').prepend('<a class="pull-right btn btn-mini btn-success" onclick="AWS.User.delete_draft(\'\', \'clean\');">' + _t('清空所有') + '</a>');
				}
			break;

			case "#site_announce__detail":
				var request_url = G_BASE_URL + '/home/ajax/site_announce_detail/id-'+ site_id;
				$('.mod-footer').hide();
				break;
            case "#site_announce__list":
                var request_url = G_BASE_URL + '/home/ajax/site_announce_list/page-' + $(this).attr('data-page');
                break;
			case '#invite_list__invite':
				var request_url = G_BASE_URL + '/home/ajax/invite/page-' + $(this).attr('data-page');
			break;

			case '#focus_topic__focus':
				var request_url = G_BASE_URL + '/topic/ajax/focus_topics_list/page-' + $(this).attr('data-page');
			break;
		}

		$(this).addClass('loading');

		if (window.location.hash == '#focus_topic__focus')
		{
			$('.aw-feed-list').addClass('aw-topic-list');

		}
		else
		{
			$('.aw-feed-list').removeClass('aw-topic-list');
		}

		$.get(request_url, function (response)
		{
			if (response.length)
			{

				if ($(_this).attr('data-page') == 0)
				{
					$('#main_contents').html(response);

                    window._bd_share_config={
                        "common":{
                            bdText : '',
                            bdDesc : '',
                            bdUrl : '',
                            onBeforeClick:function(cmd,config){
                                return {bdText:$('#share_content').val(),
                                    bdDesc:$('#share_title').val(),
                                    bdUrl:$('#share_url').val()
                                }
                            }
                        },
                        "share":{}
                    };
                    with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src=document.location.protocol + '//'+ window.location.host +'/bdimgshare/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];

                }
				else
				{
					$('#main_contents').append(response);
                    window._bd_share_main.init();
                    window._bd_share_config={
                        "common":{
                            bdText : '',
                            bdDesc : '',
                            bdUrl : '',
                            onBeforeClick:function(cmd,config){
                                return {bdText:$('#share_content').val(),
                                    bdDesc:$('#share_title').val(),
                                    bdUrl:$('#share_url').val()
                                }
                            }
                        },
                        "share":{}
                    };
                    with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src=document.location.protocol + '//'+ window.location.host +'/bdimgshare/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];

                }


				$(_this).attr('data-page', parseInt($(_this).attr('data-page')) + 1);
			}
			else
			{
				if ($(_this).attr('data-page') == 0)
				{
					$('#main_contents').html('<p style="padding: 15px 0" align="center">' + _t('没有内容') + '</p>');
				}

				$(_this).addClass('disabled');

				$(_this).find('span').html(_t('没有更多了'));
			}

			$(_this).removeClass('loading');
		})

		return false;
	});


	if ($('.aw-mod.side-nav a[rel="' + window.location.hash.replace(/#/g, '') + '"]').attr('href'))
	{
		$('.aw-mod.side-nav a[rel="' + window.location.hash.replace(/#/g, '') + '"]').click();
	}
	else
	{
		$('.aw-mod.side-nav a[rel=all]').click();
	}

	//问题添加评论
    AWS.Init.init_comment_box('.aw-add-comment');
});

function _welcome_step_1_form_processer(result)
{
	welcome_step('2');
}

function welcome_step(step)
{
	switch (step)
	{
		case '1':
			var fileupload = new FileUpload('avatar', $('#welcome_avatar_uploader'), $("#aw-upload-img"), G_BASE_URL + '/account/ajax/avatar_upload/', {'loading_status' : '#aw-img-uploading'});
		break;

		case '2':
			$('#welcome_topics_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');

			$('.aw-first-login').hide().siblings().eq(1).show();

			$.get(G_BASE_URL + '/account/ajax/welcome_get_topics/', function (result) {
				$('#welcome_topics_list').html(result);
			});
		break;

		case '3':
			$('#welcome_users_list').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');

			$('.aw-first-login').hide().siblings().eq(2).show();

			$.get(G_BASE_URL + '/account/ajax/welcome_get_users/', function (result) {
				$('#welcome_users_list').html(result);
			});
		break;

		case 'finish':
			$('#aw-ajax-box').html('');
			$('.modal-backdrop').detach();
			$('body').removeClass('modal-open');

			$.get(G_BASE_URL + '/account/ajax/clean_first_login/', function (result){});
		break;
	}
}

function reload_list()
{	
	$('#main_contents').html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');

	$('#bp_more').attr('data-page', 0).click();
}

function check_actions_new(time)
{
	$.get(G_BASE_URL + '/home/ajax/check_actions_new/time-' + time, function (result)
	{
		if (result.errno == 1)
		{
			if (result.rsm.new_count > 0)
			{
				if ($('#new_actions_tip').is(':hidden'))
				{
					$('#new_actions_tip').css('display', 'block');
				}

				$('#new_action_num').html(result.rsm.new_count);
			}
		}
	}, 'json');
}
