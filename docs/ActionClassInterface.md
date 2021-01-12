[comment]: # (This file is part of Gectrl, PHP Genereric controller. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)

##  Interface ActionClassInterface

Prescribe [Gectrl] strategy (application logic) actionClasses contract,<br>
* applied as the ([Gectrl]) invoke of condition **evaluation** and opt, logic **doAction** methods

Classes implementing the interface may also
* extend a baseClass
* implement other interface(s)
* use the singleton pattern
* ....

For example, please review **test/AcSrc/ActionExampleTest.php**.

#### Interface methods

```
getExecOrder()
```
* Return _int_, unique, implement class (methods) will be invoked in order
* _static_

Method getExecOrder MUST return an unique execOrder (int) number,<br>
class (_evaluate_ /  _doAction_ methods) will be invoked in order.<br>
Use a greater int interval sequence. 
    
For a low execOrder return number (first?) and the 'evaluate' method simply ```return true;``` :<br>
may the 'doAction' method be used for
* input sanitation, validation, assert...
*  single-load multi-use work resource (stored, with key, in _[Package]_ workData)

For a high execOrder return number (last?) and the 'evaluate' method simply ```return true;``` :<br>
may the 'doAction' method be used for
* 'default' action
* final (output) preparation

___

One or both of **evaluate** / **doAction** methods may be a factory method or not...

```
evaluate( package )
```
* Evaluates application logic invoke condition 
* ```package``` _[Package]_ (passed as reference)
* Return _bool_, true will cause [Gectrl] to invoke the _doAction_ method (below), false not
* _static_

```
doAction( package )
```
* Application logic, will be invoked if method _evaluate_ (above) return true
* ```package``` _[Package]_ (passed as reference)
* Return _bool_, true will force [Gectrl] exec break and return the package, false not
* _static_

---
Go to [README], [Gectrl], [Package] docs.

[Gectrl]:Gectrl.md
[Package]:Package.md
[README]:../README.md
