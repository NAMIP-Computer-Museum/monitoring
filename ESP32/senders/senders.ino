/*********
  Rui Santos
  Complete project details at https://RandomNerdTutorials.com/esp32-lora-sensor-web-server/
  
  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files.
  
  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.
*********/

//bibliothèques pour le LoRa
#include <LoRa.h>
#include "boards.h"

// Inclure les bibliothèques nécessaires

#include <Adafruit_Sensor.h>
//bibliothèques pour BME280
#include <Adafruit_BME280.h>
//bibliothèques pour Si7021
#include <Adafruit_Si7021.h>


// Définir les constantes et les variables globales
const int buttonPin = 13;

//BME280 definition
//Adafruit_BME280 bme;

//Si7021 definition
Adafruit_Si7021 Si;

//packet counter et pour le LoRA
int readingID = 0;
int counter = 0;

String LoRaMessage = "";

// Clef 
String apiKeyValue = "xxx";

// ID de l'appareil
String id = "1";

float temperature = 0;
float humidity = 0;
float pressure = 0;

//Temps avant chaque envoie de boucle (en milliseconde)
const unsigned long sendInterval = 1200000;

//Variable pour le bon fonctionnement de la boucle
unsigned long elapsedTime = 0;
unsigned long sleepStartMillis = 0;

//Initialize LoRa module
void startLoRA(){
  //setup LoRa transceiver module
  LoRa.setPins(RADIO_CS_PIN, RADIO_RST_PIN, RADIO_DIO0_PIN);

  while (!LoRa.begin(LoRa_frequency) && counter < 10) {
    Serial.print(".");
    counter++;
    delay(500);
  }
  if (counter == 10) {
    // Increment readingID on every new reading
    readingID++;
    Serial.println("Starting LoRa failed!"); 
  }
  Serial.println("LoRa Initialization OK!");
  delay(2000);
}
/*
void startBME(){
  bool status1 = bme.begin();  
  if (!status1) {
    Serial.println("Could not find a valid BME280_1 sensor, check wiring!");
    while (1);
  }
}
*/

void startSi(){
  bool status1 = Si.begin();  
  if (!status1) {
    Serial.println("Could not find a valid Si7021_1 sensor, check wiring!");
    while (1);
  }
}


void getReadings(){
  
  //temperature = bme.readTemperature();
  // humidity = bme.readHumidity();
  //pressure = bme.readPressure() / 100.0F;

  temperature = Si.readTemperature();
  humidity = Si.readHumidity();
}

void convertFloatToString(float& temperature, float& humidity, char* tempChar, char* humiChar) {
  dtostrf(temperature, 6, 2, tempChar);
  dtostrf(humidity, 6, 2, humiChar);
  char tempMessage[30];
  strcpy(tempMessage, "temp: ");
  strcat(tempMessage, tempChar);
  strcat(tempMessage, " °C");

}

void displayReadings(){
  char tempChar[30];
  char humiChar[30];
  char idMessage[30];
  convertFloatToString(temperature, humidity, tempChar, humiChar);

  char tempMessage[30];
  char humiMessage[30];
  strcpy(tempMessage, "Temp:");
  strcat(tempMessage, tempChar);
  strcat(tempMessage, " °C");

  strcpy(humiMessage, "Humi:");
  strcat(humiMessage, humiChar);
  strcat(humiMessage, " %");

  strcpy(idMessage, "ID: ");
  strcat(idMessage, id.c_str());

    u8g2->setFont(u8g2_font_helvB12_tr);
    u8g2->clearBuffer();
    u8g2->drawStr(0, 15, idMessage);
    u8g2->drawStr(0, 35,tempMessage);
    u8g2->drawStr(0, 60,humiMessage);
    u8g2->sendBuffer();
    
    delay(2000); // Attendez 2 secondes

    u8g2->clearBuffer(); 
    u8g2->sendBuffer(); 

}

void sendReadings() {
  LoRaMessage = "api_key=" + apiKeyValue  + "&temperature=" + String(temperature) + "&humidity=" + String(humidity) + "&id=" + id + "";
  //Send LoRa packet to receiver
  LoRa.beginPacket();
  LoRa.print(LoRaMessage);
  LoRa.endPacket();
  Serial.println(LoRaMessage);
 
}

void goToSleep(unsigned long sleepTime) {
  sleepStartMillis = millis();
  esp_sleep_enable_timer_wakeup(sleepTime * 1000);
  esp_sleep_enable_ext0_wakeup((gpio_num_t)buttonPin, LOW);
  esp_light_sleep_start();
}

void setup() {
  //initialize Serial Monitor
  initBoard();
  //startBME();
  startSi();
  startLoRA();
  pinMode(buttonPin, INPUT_PULLUP);
  getReadings();
  goToSleep(sendInterval);
}


void loop() {


 unsigned long currentMillis = millis();

  // Vérifie si l'ESP32 vient de se réveiller à cause d'un appui sur le bouton
  if (esp_sleep_get_wakeup_cause() == ESP_SLEEP_WAKEUP_EXT0) {
    delay(50); // Ajouter un petit délai pour éviter le rebond du bouton
    elapsedTime += (currentMillis - sleepStartMillis);
    displayReadings();
    goToSleep(sendInterval - elapsedTime / 1000);
  }

  // Si l'ESP32 vient de se réveiller à cause du timer, envoyer les données
  if (esp_sleep_get_wakeup_cause() == ESP_SLEEP_WAKEUP_TIMER) {
    getReadings();
    sendReadings();
    delay(10);
    elapsedTime = 0;
    goToSleep(sendInterval);
  }



}
