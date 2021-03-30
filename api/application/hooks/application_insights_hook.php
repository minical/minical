<?php

class Application_insights_hook {
	
	private $_telemetryClient;
	
	public function setHandler() {
		
		if (!class_exists("\ApplicationInsights\Telemetry_Client")) {
			return;
		}
		
		$this->_telemetryClient = new \ApplicationInsights\Telemetry_Client();
		$this->_telemetryClient->getContext()->setInstrumentationKey(INSTRUMENTATION_KEY);
		
		set_exception_handler(array($this, 'exceptionHandlerApplicationInsights'));
		
		register_shutdown_function(array($this, 'handleShutdown'));
	}
	
	public function exceptionHandlerApplicationInsights(\Exception $exception) {
		if ($exception != NULL) {
			$this->_telemetryClient->trackException($exception);
			$this->_telemetryClient->flush();
		}
	}
	
	public function handleShutdown() {
		
		$url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$requestName = $_SERVER["REQUEST_URI"];
		$startTime = $_SERVER["REQUEST_TIME"];
		$duration = (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000;

		$this->_telemetryClient->trackRequest($requestName, $url, $startTime, $duration, http_response_code(), !(http_response_code() == 404));
		// Flush all telemetry items
		$this->_telemetryClient->flush();
	}
}

