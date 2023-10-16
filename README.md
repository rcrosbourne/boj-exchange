# BOJ Exchange Rate Converter
[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F12a0e6d2-cc55-4f0b-bf2b-63f2cf1e1031%3Fdate%3D1&style=for-the-badge)](https://forge.laravel.com/servers/700492/sites/2041744)


## Description
BOJ Exchange Rate Converter is a tool that fetches the daily currency rate from the [Bank of Jamaica website](https://boj.org.jm/market/foreign-exchange/counter-rates/) and allows users to convert any amount from one currency to another, both of which are supported by the website.

## Live Demo
Experience the BOJ Exchange Rate Converter in action. Check out the live version [here](https://bojexchange.rcrosbourne.dev/).

## Features
- **User Interface:** Provides a user-friendly page where users can input the amount they wish to convert, choose the desired currency, and specify the date for which the conversion should be based.
- **Real-time Conversion:** Fetches the current exchange rates directly from the Bank of Jamaica website.
- **Detailed Results:** Users are presented with the converted amount and the exchange rate used for the computation as soon as the amount to be converted is changed.

## Coming Soon
- **Trend Analysis:** Offer trend analysis for widely used currencies, providing insights into historical patterns.
- **AI Predictions:** Leverage artificial intelligence to provide predictions on whether the exchange rate is likely to trend upwards or downwards in the near future.
- **API Endpoints:** Introduce API endpoints for external applications to easily integrate and fetch conversion rates.

## Installation
Follow these steps to get the BOJ Exchange Rate Converter running on your local machine:

1. **Clone the Repository:** 
   ```
   git clone https://github.com/rcrosbourne/boj-exchange
   ```

2. **Install Composer Dependencies:**
   ```
   composer install
   ```

3. **Install NPM Packages:**
   ```
   npm install
   ```

4. **Compile Assets:**
   ```
   npm run dev
   ```

## Usage
After installation, or when accessing the live site, simply enter the amount you wish to convert. The conversion is real-time and occurs as you change the amount. Select the currency and desired date, and the tool will automatically display the converted amount and the exchange rate used.

## Contribution
If you'd like to contribute to this project, please fork the repository and submit a pull request. We appreciate any and all contributions!

## License
Please check the repository for licensing details.
