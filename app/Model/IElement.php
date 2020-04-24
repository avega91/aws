<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 8/3/17
 * Time: 6:26 PM
 */
class IElement extends AppModel {
    public $name = 'IElement';

    const Is_Conveyor = 1;
    const Is_Photo = 2;
    const Is_Video = 3;
    const Is_Folder = 4;
    const Is_Report = 5;
    const Is_Note = 6;
    const Is_CompanyColaborator = 7;
    const Is_News = 8;
    const Is_WelcomeMsg = 10;
    const Is_ContactMsg = 11;
    const Is_File = 12;
    const Is_UltrasonicData = 13;
    const Is_Saving = 14;
    const Is_History = 15;
    const Is_Profile = 16;
    const Is_SmartReport = 19;
    const Is_CustomReport = 20;
    const Is_Customer = 21;
    const Is_Calculator = 22;
    const Is_MinuteMan = 23;
    const Is_ContiUniversity = 24;
    const Is_UsersSection = 25;
    const Is_AdvancedSection = 26;
    const Is_Notification = 27;
    const Is_HelpSection = 28;
    const Is_TermsSection = 29;
    const Is_GeneralReportPerConveyor = 30;
    const Is_ReportsPerCustomer = 31;
    const Is_QRCode = 32;
    const Is_UltrasonicWithGauge = 33;
    const Is_LifetimeEstimate = 34;
    const Is_RecommendedBelt = 35;
    const Is_Tutorial = 36;
    const Is_TechnicalData = 37;
    const Is_UltrasonicReport = 38;
    const Is_ScheduledNotifications = 39;
    const Is_Distributor = 40;
}