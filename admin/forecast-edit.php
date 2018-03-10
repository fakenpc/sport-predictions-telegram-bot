<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header"><?=isset($_GET['id']) ? 'Изменить' : 'Добавить'?> прогноз</h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../CapperDB.php';
						require_once __DIR__.'/../ForecastDB.php';

						use Longman\TelegramBot\DB;
						
						CapperDB::initializeCapper();
						ForecastDB::initializeForecast();

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

							if(isset($_POST['submit']))
							{

								// if edit forecast
								if(isset($_GET['id'])) {
									$forecast_id = intval($_GET['id']);
									$result = ForecastDB::updateForecast(['capper_id' => $_GET['capper_id'], 'name' => $_POST['name'], 'description' => $_POST['description'], 'sending_timestamp' => strtotime($_POST['sending_timestamp']), 'disabling_timestamp' => strtotime($_POST['disabling_timestamp'])], ['id' => $forecast_id]);
								// if add forecast
								} else {
									$result = ForecastDB::insertForecast($_GET['capper_id'], $_POST['name'], $_POST['description'], strtotime($_POST['sending_timestamp']), strtotime($_POST['disabling_timestamp']), 0);
								}

								if($result) {
									print "<div class='alert alert-success' role='alert'>Каппер успешно добавлен/обновлен !</div>";
								}
								

							}
						
							$id = '';
							$name = '';
							$description = '';
							$sending_timestamp = 0;
							$disabling_timestamp = 0;


							// edit capper
							if(isset($_GET['id'])) {
								$forecasts = ForecastDB::selectForecast($_GET['id']);

								if(count($forecasts)) {
									$forecast = $forecasts[0];
									$id = $forecast['id'];
									$name = $forecast['name'];;
									$description = $forecast['description'];;
									$sending_timestamp = $forecast['sending_timestamp'];;
									$disabling_timestamp = $forecast['disabling_timestamp'];;
								}
								
							
							}

							print '
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<a href="forecast.php?capper_id='.$_GET['capper_id'].'">Назад</a>
										<form enctype="multipart/form-data" action="?capper_id='.$_GET['capper_id'].'&'.($id ? 'id='.$id : '#').'" method="POST">
											<div class="form-group">
												<label for="name">Ординар / эксперсс / ординарэкспресс</label>
												<input type="text" class="form-control" name="name" placeholder="Имя" value="'.$name.'">
											</div>
											<div class="form-group">
												<label for="description">Описание</label>
												<textarea class="form-control" rows="5" name="description" placeholder="Описание">'.$description.'</textarea>
											</div>
											<div class="form-group">
												<label for="sending_timestamp">Время отправки прогноза</label>
												<input type="datetime" class="form-control" name="sending_timestamp" placeholder="Время отправки" value="'.($sending_timestamp ? date('Y-m-d H:i:s', $sending_timestamp) : date('Y-m-d H:i:s')).'">
											</div>
											<div class="form-group">
												<label for="sending_timestamp">Время потери актуальности</label>
												<input type="datetime" class="form-control" name="disabling_timestamp" placeholder="Время отправки прогноза" value="'.($disabling_timestamp ? date('Y-m-d H:i:s', $disabling_timestamp) : date('Y-m-d H:i:s')).'">
											</div>
											<button type="submit" name="submit" class="btn btn-primary">Submit</button>
										</form>
									</div>
								</div>
							'; 

							
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