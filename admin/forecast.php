<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header">Прогнозы каппера</h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../CapperDB.php';
						require_once __DIR__.'/../ForecastDB.php';
						require_once __DIR__.'/../SubscriptionDB.php';

						use Longman\TelegramBot\DB;
						
						CapperDB::initializeCapper();
						ForecastDB::initializeForecast();
						SubscriptionDB::initializeSubscription();

						if(!file_exists(__DIR__.'/../config.php')) {
						    die("Please rename example_config.php to config.php and try again. \n");
						} else {
						    require_once __DIR__.'/../config.php';
						}

						try {
						    // Create Telegram API object
						    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

						    // Enable MySQL
    						$telegram->enableMySql($mysql_credentials);

    						if(isset($_GET['remove_forecast_id'])) {
    							ForecastDB::deleteForecast($_GET['remove_forecast_id']);
    						}

    						@$capper_id = intval($_GET['capper_id']);

							$cappers = CapperDB::selectCapper($capper_id);

							if(count($cappers)) {
								$capper = $cappers[0];

								print '
								<div class="row placeholders">
									<div class="col-xs-12 col-sm-6 placeholder">
										<a href="capper-edit.php?id='.$capper['id'].'">
											<img src="'.$images_dir.current(glob($images_dir.$capper['id'].'.*')).'" class="img-responsive" alt="Generic placeholder thumbnail">
										</a>
										<h4>'.$capper['name'].'</h4>
										<span class="text-muted">'.$capper['description'].'</span>
									</div>
								</div>
								';

								print '
								<h2 class="sub-header">Прогнозы</h2>
								<a href="forecast-edit.php?capper_id='.$capper['id'].'">Добавить</a>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Ординар / эксперсс / ординарэкспресс</th>
												<th>Описание прогноза</th>
												<th>Время отправки прогноза</th>
												<th>Время потери актуальности прогноза</th>
												<th>Редактировать</th>
											</tr>
										</thead>
										<tbody>
								';
								$forecasts = ForecastDB::selectForecast(null, $capper_id);

								foreach ($forecasts as $forecast) {
									$image_filename = current(glob($images_dir.'forecast_'.$forecast['id'].'.*'));
									$image_location = $images_dir.$image_filename;

									print '
										<tr>
											<td>'.$forecast['id'].'</td>
											<td>
												<pre>'.$forecast['name'].'</pre>
												'. ($image_filename ? '
													<button class="btn btn-default" data-toggle="collapse" data-target="#hide-forecast-'.$forecast['id'].'-image">Показать изображение</button>
													<div id="hide-forecast-'.$forecast['id'].'-image" class="collapse">
														<img src="'.$image_location.'" class="img-responsive" alt="Generic placeholder thumbnail">
													</div>
												' : '') .'
												
											</td>
											<td><pre>'.$forecast['description'].'</pre></td>
											<td>'.date('Y-m-d H:i:s', $forecast['sending_timestamp']).'</td>
											<td>'.date('Y-m-d H:i:s', $forecast['disabling_timestamp']).'</td>
											<td>
												<a href="forecast-edit.php?id='.$forecast['id'].'&capper_id='.$capper['id'].'">Изменить</a> 
												<a href="forecast.php?remove_forecast_id='.$forecast['id'].'&capper_id='.$capper['id'].'">Удалить</a> 
											</td>
										</tr>

										
									';
								}

								print '
								</div>	
								';

							} else {
								print 'Каппер не найден';
							}

							
						} catch (Longman\TelegramBot\Exception\TelegramException $e) {
						    echo $e->getMessage();
						    // Log telegram errors
						    Longman\TelegramBot\TelegramLog::error($e);
						} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
						    // Catch log initialisation errors
						    echo $e->getMessage();
						}	
					
					?>
				</div>
			</div>
		</div>

<?php require_once __DIR__.'/footer.php'; ?>		