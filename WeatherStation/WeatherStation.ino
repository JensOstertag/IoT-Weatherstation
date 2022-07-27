// WiFi
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
ESP8266WiFiMulti wifiMulti;

// BME280
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>
Adafruit_BME280 bme;

// API Connection
String apiServer = "API_SERVER";

// Config
#define connectionTimeout 5000
#define pushAttempts 5
#define deepSleepMinutes 15
String stationKey = "STATION_KEY";
String stationAuth = "STATION_AUTHENTICATION";

// Weather Data
float temperature;
float humidity;
float pressure;

// LED
#define connectWiFiPin 14
#define uploadDataPin 12

void setup() {
  Serial.begin(115200);

  pinMode(connectWiFiPin, OUTPUT);
  pinMode(uploadDataPin, OUTPUT);

  digitalWrite(connectWiFiPin, LOW);
  digitalWrite(uploadDataPin, LOW);

  splashScreen();

  if(checkHardware() == false) {
    Serial.println("[SETUP] Hardware Check Failed");
    sleep();
  }

  connectWiFi();

  digitalWrite(connectWiFiPin, HIGH);

  setWeatherData();
  pushData();

  digitalWrite(uploadDataPin, HIGH);

  delay(10);

  sleep();
}

void loop() {}

boolean checkHardware() {
  int badHardwareCounter = 0;
  while(!(bme.begin(0x76))) {
    badHardwareCounter++;
    if(badHardwareCounter >= 5) {
      return false;
    }
    delay(100);
  }

  return true;
}

void connectWiFi() {
  wakeUp();

  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  
  wifiMulti.addAP("SSID1", "PASSWORD1");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID2", "PASSWORD2");           // ADD ACCESSPOINT
  wifiMulti.addAP("SSID3", "PASSWORD3");           // ADD ACCESSPOINT

  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("##################################################");
  Serial.println("[WIFI] Connecting");

  if(wifiMulti.run(5000) != WL_CONNECTED) {
    Serial.println("[WIFI] Could not connect to WiFi");
    sleep();
  }
  
  Serial.println();
  Serial.print("[WIFI] Connected to: ");
  Serial.println(WiFi.SSID());
  Serial.print("[WIFI] IP Address: ");
  Serial.println(WiFi.localIP());
  Serial.println("##################################################");
  for(int i = 0; i < 3; i++){Serial.println();}
}

void setWeatherData() {
  temperature = bme.readTemperature();
  humidity = bme.readHumidity();
  pressure = bme.readPressure() / 100;
}

void pushData() {
  String request = apiServer + "/api/push-data.php";
  request += "?key=" + stationKey;
  request += "&auth=" + stationAuth;
  request += "&temp=" + String(temperature);
  request += "&humi=" + String(humidity);
  request += "&pres=" + String(pressure);

  Serial.println("[API] Pushing Data");

  WiFiClient wifi;
  HTTPClient http;
  http.begin(wifi, request);

  int httpCode = 0;
  int attempt = 0;
  
  do {
    httpCode = http.GET();
  } while(httpCode != HTTP_CODE_OK && attempt < pushAttempts);

  http.end();
}

void wakeUp() {
  WiFi.mode(WIFI_STA);
}

void sleep() {
  WiFi.mode(WIFI_OFF);
  Serial.println("[DEEPSLEEP] Enabled deepsleep mode");
  ESP.deepSleep(deepSleepMinutes * 60 * 1000000);
}

void splashScreen() {
  for(int i = 0; i < 3; i++){Serial.println();}
  Serial.println("###################################################");
  Serial.println("###                                             ###");
  Serial.println("###   Project: IoT Weather Station              ###");
  Serial.println("###   Version: v3.0                             ###");
  Serial.println("###   Board: WeMos D1 MINI                      ###");
  Serial.println("###   Author: Jens Ostertag                     ###");
  Serial.println("###   GitHub: https://github.com/JensOstertag   ###");
  Serial.println("###                                             ###");
  Serial.println("###################################################");
  for(int i = 0; i < 3; i++){Serial.println();}
}
