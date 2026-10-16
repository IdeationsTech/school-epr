import {readFile,writeFile} from 'node:fs/promises';
const school=process.argv[2]==='--school',target=school?'school':'admin',prefix=school?'school':'admin';
const file=new URL('../resources/css/'+target+'-theme-tokens.css',import.meta.url);
const options={accent:'accent',teal:'teal',purple:'purple',blue:'blue',background:'bg',surface:'surface',text:'text',muted:'muted'};
const args=process.argv.slice(school?3:2);
if(!args.length||args.includes('--help')){
 console.log('Usage: npm run theme:'+target+' -- --accent "'+(school?'#ffc8f7':'#ff7951')+'" --teal "'+(school?'#194b70':'#00c5b5')+'"');
 console.log('Options: '+Object.keys(options).map(k=>'--'+k+' "#rrggbb"').join(' '));
 console.log('Changes only '+target+' CSS tokens. Run npm run build afterwards.');
 process.exit(0);
}
const updates={};
for(let i=0;i<args.length;i+=2){
 const key=args[i]?.replace(/^--/,'');const value=args[i+1];
 if(!args[i]?.startsWith('--')||!Object.hasOwn(options,key)||!/^#[0-9a-f]{6}$/i.test(value??'')){
  console.error('Invalid option or color. Use --help; colors must be quoted six-digit hex values. No file was changed.');process.exit(1);
 }
 updates[options[key]]=value.toLowerCase();
}
const mix=(a,b,ratio)=>'#'+[1,3,5].map(i=>Math.round(parseInt(a.slice(i,i+2),16)*(1-ratio)+parseInt(b.slice(i,i+2),16)*ratio).toString(16).padStart(2,'0')).join('');
let css=await readFile(file,'utf8');
const get=k=>updates[k]??css.match(new RegExp('--'+prefix+'-'+k+':([^;]+);'))?.[1];
for(const k of ['accent','teal','purple','blue'])if(updates[k])updates[k+'-soft']=mix(updates[k],get('bg'),.84);
if(updates.purple)updates['purple-text']=mix(updates.purple,'#ffffff',.35);
if(updates.accent){
 updates['accent-hover']=mix(updates.accent,'#ffffff',.25);updates.focus=mix(updates.accent,'#ffffff',.4);
 const luminance=hex=>[1,3,5].map(i=>parseInt(hex.slice(i,i+2),16)/255).map(v=>v<=.04045?v/12.92:((v+.055)/1.055)**2.4).reduce((n,v,i)=>n+v*[.2126,.7152,.0722][i],0);
 const l=luminance(updates.accent);updates['accent-ink']=(l+.05)/.05>=1.05/(l+.05)?'#000000':'#ffffff';
}
for(const [key,value] of Object.entries(updates)){
 const pattern=new RegExp('(--'+prefix+'-'+key+':)[^;]+;');
 if(!pattern.test(css))throw new Error('Missing token '+key+'; file was not changed.');
 css=css.replace(pattern,(_,prefix)=>prefix+value+';');
}
await writeFile(file,css);
console.log('Updated '+target+' tokens: '+Object.keys(updates).join(', ')+'. Run npm run build.');
