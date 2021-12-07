
#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Wire.h>
// Fingerprint for demo URL, expires on June 2, 2021, needs to be updated well before this date

ESP8266WiFiMulti WiFiMulti;

#define TRIGGER_TEMP_MEASURE_HOLD  0xE3
#define TRIGGER_HUMD_MEASURE_HOLD  0xE5
#define TRIGGER_TEMP_MEASURE_NOHOLD  0xF3
#define TRIGGER_HUMD_MEASURE_NOHOLD  0xF5
#define WRITE_USER_REG  0xE6
#define READ_USER_REG  0xE7
#define SOFT_RESET  0xFE
#define ERROR_I2C_TIMEOUT   998
#define ERROR_BAD_CRC   999
#define HTU21D_ADDRESS 0x40 
#define MAX_WAIT 100
#define DELAY_INTERVAL 20  //ZS 14.3.2018: increased from 10 to 20
#define MAX_COUNTER (MAX_WAIT/DELAY_INTERVAL)


void setup() 
{

  Serial.begin(115200);
  // Serial.setDebugOutput(true);

  Serial.println();
  Serial.println();
  Serial.println();

  for (uint8_t t = 4; t > 0; t--) {
    Serial.printf("[SETUP] WAIT %d...\n", t);
    Serial.flush();
    delay(1000);
  }

  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP("R", "ada2342003");
}

//Given a command, reads a given 2-byte value with CRC from the HTU21D
uint16_t readValue(byte cmd)
{

  TwoWire *_i2cPort;
  //Request a humidity reading
  _i2cPort->beginTransmission(HTU21D_ADDRESS);
  _i2cPort->write(cmd); //Measure value (prefer no hold!)
  _i2cPort->endTransmission();
  
  //Hang out while measurement is taken. datasheet says 50ms, practice may call for more
  byte toRead;
  byte counter;
  for (counter = 0, toRead = 0 ; counter < MAX_COUNTER && toRead != 3 ; counter++)
  {
    delay(DELAY_INTERVAL);

    //Comes back in three bytes, data(MSB) / data(LSB) / Checksum
    toRead = _i2cPort->requestFrom(HTU21D_ADDRESS, 3);
  }

  if (counter == MAX_COUNTER) return (ERROR_I2C_TIMEOUT); //Error out
  byte msb, lsb, checksum;
  msb = _i2cPort->read();
  lsb = _i2cPort->read();
  checksum = _i2cPort->read();

  uint16_t rawValue = ((uint16_t) msb << 8) | (uint16_t) lsb;

  if (checkCRC(rawValue, checksum) != 0) return (ERROR_BAD_CRC); //Error out

  return rawValue & 0xFFFC; // Zero out the status bits
} 


//Give this function the 2 byte message (measurement) and the check_value byte from the HTU21D
//If it returns 0, then the transmission was good
//If it returns something other than 0, then the communication was corrupted
//From: http://www.nongnu.org/avr-libc/user-manual/group__util__crc.html
//POLYNOMIAL = 0x0131 = x^8 + x^5 + x^4 + 1 : http://en.wikipedia.org/wiki/Computation_of_cyclic_redundancy_checks
#define SHIFTED_DIVISOR 0x988000 //This is the 0x0131 polynomial shifted to farthest left of three bytes

byte checkCRC(uint16_t message_from_sensor, uint8_t check_value_from_sensor)
{
  //Test cases from datasheet:
  //message = 0xDC, checkvalue is 0x79
  //message = 0x683A, checkvalue is 0x7C
  //message = 0x4E85, checkvalue is 0x6B
  uint32_t remainder = (uint32_t)message_from_sensor << 8; //Pad with 8 bits because we have to add in the check value
  remainder |= check_value_from_sensor; //Add on the check value
  uint32_t divsor = (uint32_t)SHIFTED_DIVISOR;
  for (int i = 0 ; i < 16 ; i++) //Operate on only 16 positions of max 24. The remaining 8 are our remainder and should be zero when we're done.
  {
    //Serial.print("remainder: ");
    //Serial.println(remainder, BIN);
    //Serial.print("divsor:    ");
    //Serial.println(divsor, BIN);
    //Serial.println();
    if ( remainder & (uint32_t)1 << (23 - i) ) //Check if there is a one in the left position
      remainder ^= divsor;
    divsor >>= 1; //Rotate the divsor max 16 times so that we have 8 bits left of a remainder
  }
  return (byte)remainder;
}
 

////////////////

void loop() {
  // wait for WiFi connection
  if ((WiFiMulti.run() == WL_CONNECTED)) 
  {

   //CO2 SENSOR

 int sensorValue = analogRead(A0); 
 int mv_voltage = sensorValue*(3200/1023.0);
 int instConc = -1; //default

 if(mv_voltage < 400) 
    {
      Serial.println("cekam");
    }
    else 
    {
      int mv_voltage_diference = mv_voltage - 400;
      instConc = mv_voltage_diference * 50.0 / 16.0;    // Read temperature as Celcius??
      Serial.print("CO2 = ");
      Serial.print(instConc);
      Serial.println(" ppm)"); 
    } 


    //TEMPERATURE SENSOR
  float realTemperature = -1;
 /*   
  uint16_t rawTemperature = readValue(TRIGGER_TEMP_MEASURE_NOHOLD);
  if(rawTemperature == ERROR_I2C_TIMEOUT || rawTemperature == ERROR_BAD_CRC) 
  {
    realTemperature = -1;
  }
  else
  {
    //Given the raw temperature data, calculate the actual temperature
    float tempTemperature = rawTemperature * (175.72 / 65536.0); //2^16 = 65536
    realTemperature = tempTemperature - 46.85; //From page 14 

     Serial.print("Temperature = ");
      Serial.print(realTemperature);
      Serial.println(" C)"); 
      
  } */
  
    ///////////////////
    // mam senzory - jen zapisu do databaze
    

    std::unique_ptr<BearSSL::WiFiClientSecure>client(new BearSSL::WiFiClientSecure);
    client->setInsecure();
    HTTPClient https;

    Serial.print("[HTTPS] begin...\n");
    String url = "https://www.rojicek.cz/dbstore/sens.php?temperature=0&co2="; 
    url = url + instConc;
    
    
    
    if (https.begin(*client, url)) 
    {  
      // HTTPS
      Serial.print("[HTTPS] GET...\n");
      
      // start connection and send HTTP header
      int httpCode = https.GET();

      // httpCode will be negative on error
      if (httpCode > 0) 
      {
        // HTTP header has been send and Server response header has been handled
        Serial.printf("[HTTPS] GET... code: %d\n", httpCode);

        // file found at server
        if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) 
        {
          String payload = https.getString();
          Serial.println(payload);
        }
      } 
      else 
      {
        Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }

      https.end();
    }
    else 
    {
      Serial.printf("[HTTPS] Unable to connect\n");
    }
  }

  Serial.println("Wait for next round...");
  delay(300000);
}
