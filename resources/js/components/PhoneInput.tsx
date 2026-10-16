function nationalPhone(value:string){
 let digits=value.replace(/[^0-9]/g,'');
 if(value.trim().startsWith('+92'))digits=digits.slice(2);
 else if(digits.startsWith('0092'))digits=digits.slice(4);
 else if(digits.startsWith('92')&&(digits.length===2||digits.length>10))digits=digits.slice(2);
 if(digits.startsWith('0'))digits=digits.slice(1);
 return digits.slice(0,10);
}
export function PhoneInput({value,onChange,label='Phone',required=true,mobileOnly=false}:{value:string;onChange:(value:string)=>void;label?:string;required?:boolean;mobileOnly?:boolean}){
 function update(input:HTMLInputElement,value:string){const digits=nationalPhone(value);input.value=digits;onChange(digits?`+92${digits}`:'');}
 return <div className="phone-input"><span aria-hidden="true">+92</span><input aria-label={label} type="tel" inputMode="numeric" autoComplete="tel-national" required={required} pattern={mobileOnly?'3[0-9]{9}':'(?:3[0-9]{9}|[124-9][0-9]{8,9})'} maxLength={10} placeholder="3001234567" title="Enter 10 digits for a mobile number or 9–10 for a landline, without the leading zero. Country code +92 is already included." value={nationalPhone(value)} onChange={e=>update(e.target,e.target.value)} onPaste={e=>{e.preventDefault();update(e.currentTarget,e.clipboardData.getData('text'));}}/></div>;
}
