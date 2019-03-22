****** Ranosys_Paytm ******

Supported Versions:2.2.X onward

Please follow the steps to enable the extension.

1)Installation and Configuration :
===>upload your extension app/code (all files and folder) at your server end.

2)Please run below commands after uploding extension to respective folder:
 i)php bin/magento module:enable Ranosys_Paytm
ii)php bin/magento setup:upgrade
iii)php bin/magento setup:static-content:deploy -f


3)Please Goto
  Admin->Store->Configuration->Sales->Payment Method->Ranosys Paytm
  ==>Please fill all the details and save it.

4)You need to create business account on paytm.For staging environment you need to create developer account.(https://dashboard.paytm.com/)

5)Goto Admin->System->Cache Management
  Clear all Cache.



