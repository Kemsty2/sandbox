<?php
session_start();
if(!isset($_SESSION['vote'])) $_SESSION['vote'] = 0;
$data = json_decode(file_get_contents('vote.data'), true);
?><!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Белочки-истерички</title>
	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
	<link rel="stylesheet" href="css/template.css" type="text/css" />
	<link rel="stylesheet" href="jqplot/jquery.jqplot.css" type="text/css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="jqplot/jquery.jqplot.min.js"></script>
	<script type="text/javascript" src="jqplot/plugins/jqplot.barRenderer.min.js"></script>
	<script type="text/javascript" src="jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
	<script type="text/javascript" src="jqplot/plugins/jqplot.pointLabels.min.js"></script>
</head>
<body>
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a href="/" class="brand"><img src="img/b.jpg" width="30" /> Белочки - истерички</a>
			</div>
		</div>
	</div>
 
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span6">
				<div class="hero-unit">
					<h1>Прими участие в тотализаторе!</h1>
					<p>Спаси от голода и истребления <a href="javascript:void(0);" id="isterika">белочек - истеричек</a>!</p><hr />
					<h4>Статистика тотализатора:
						<small>
						<br />Всего пожертвовано &mdash; $<span id="total"></span>
						<br />Спасено белочек-истеричек &mdash; <span id="qty"></span> шт.
						<br />Встречено жлобов и уродов &mdash; <span id="zhlob"></span>
						</small>
					</h4>
				</div>
			</div>
			<div class="offset6 span6">				
				<div id="chart"><div style="padding: 120px 0 0 40%;"><img src="img/load.gif" /></div></div>
			</div>
		</div>
		<div class="row-fluid">
			<?php if(isset($_SESSION['vote']) && (int)$_SESSION['vote'] <= 5): ?>
			<div class="well"><strong><i class="icon-question-sign"></i> Как Вы думаете, когда мы получим зп? Делайте Ваши ставки!</strong></div>
			<div id="fire">
				<div class="span2">
					<a href="javascript:void(0);" data-vote="0" class="btn btn-large">
						Ставка <strong>10 р.</strong><br />
						Либо получите, либо нет...
					</a>
				</div>
				<div class="span2">
					<a href="javascript:void(0);" data-vote="1" class="btn btn-large btn-info">
						Ставка <strong>1000 р.</strong><br />
						Когда-нибудь получите...
					</a>
				</div>
				<div class="span2">
					<a href="javascript:void(0);" data-vote="2" class="btn btn-large btn-success">
						Ставка <strong>$1000</strong><br />
						Уверен, это будет завтра...
					</a>
				</div>
				<div class="span2">
					<a href="javascript:void(0);" data-vote="3" class="btn btn-large btn-warning">
						Ставка <strong>500 р.</strong><br />
						100% это будет не скоро...
					</a>
				</div>
				<div class="span2">
					<a href="javascript:void(0);" data-vote="4" class="btn btn-large btn-danger">
						Ставка <strong>0</strong><br />
						Идите на хер, уже всем все дали...
					</a>
				</div>
				<div class="span2">
					<a href="javascript:void(0);" data-vote="5" class="btn btn-large btn-inverse">
						Ставка <strong>$1 000 000</strong><br />
						Все случится сегодня...
					</a>
				</div>
			</div>
			<?php else: ?>
			<div class="span3">&nbsp;</div>
			<div class="offset3 span7">
				<h3>Ахтунг! Много играть на тотализаторе &mdash; вредно для вашего бюджета!</h3>
			</div>
			<?php endif; ?>
		</div>

		<footer class="footer">
			<p class="pull-right"><a href="javascript:void(0);">&uarr; Наверх</a></p>
			<p>&copy; Анонимное Благотворительное Общество &laquo;Хочу ЗП&raquo;, 2012</p>
		</footer>
	</div>
 
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
		(function($){
			var 	load = $('#chart').html(),
				oops = '<div class="alert alert-error"><h3>Что за дерьмо???</h3><p>В жизни случается и не такое!!! Попытайся еще раз!</p><a href="javascript:void(0);" class="btn btn-danger" id="refresh"><i class="icon-refresh icon-white"></i> Обновить</a></div>',
				$fire = $('#fire'), fire = $fire.html();

			//инициализация
			$('#isterika').popover({title: "Белочки - истерички", placement: "bottom", content: "<img src=\"http://cs306209.userapi.com/v306209326/5a4/EWqrYQi8bnA.jpg\" width=\"200\" />"});
			$('#refresh').live('click', function(){
				$('#chart').trigger('drawChart');
			});

			//голосуем
			$('#chart').bind('drawChart', function(){
				var $this = $(this);

				$this.html(load);
				$fire.html(load);
				//рисуем
				$.ajax({
					url: "vote.php",
					data: { "v": $fire.data('vote') },
					type: "post",
					dataType: "json",
					success: function(data) {
						try{
							$this.html('');
							$fire.data('vote', false);

							$('#total').html(data.total);
							$('#qty').html(data.qty);
							$('#zhlob').html(data.zhlob);
							
							if(data.votes > 5) $fire.html('<div class="offset3 span7"><h3>Ахтунг! Много играть на тотализаторе &mdash; вредно для вашего бюджета!</h3></div>');
							else if(data.message) $fire.html(['<div class="lead alert alert-block"><a id="backToVote" class="btn btn-large btn-warning pull-right">Хочу проголосовать еще!</a>', data.message, '<br /></div>'].join('')); 
							else $fire.html(fire); 
							$.jqplot('chart', data.chart, {
								seriesDefaults:{
									renderer:$.jqplot.BarRenderer,
									rendererOptions: {fillToZero: true}
								},
        							axes: {
            								xaxis: {
										renderer: $.jqplot.CategoryAxisRenderer,
										ticks: ["Жлоб", "1000 р.", "$1000", "500 р.", "Урод", "Оооочень щедрый дядя..."]
									}
        							}
							});
						} catch(e) {							
							$this.html(oops);
							$fire.html(fire);
						}
					},
					error: function(){
						$this.html(oops);
						$fire.html(fire);
					}
				});
			}).trigger('drawChart');

			//обработчики голосований для кнопок
			$('[data-vote]').live('click', function(){
				$fire.data('vote', $(this).data('vote'));
				$('#chart').trigger('drawChart');
			});

			//проголосовать еще
			$('#backToVote').live('click', function(){
				$fire.html(fire);
			});
		})(jQuery);
	</script>
</body>
</html>